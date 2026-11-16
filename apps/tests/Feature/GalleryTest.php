<?php

namespace Tests\Feature;

use App\Models\GalleryAlbum;
use App\Models\GalleryMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    private function photo(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('wedding.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII='));
    }

    public function test_sidebar_has_one_gallery_module_and_empty_screen_works(): void
    {
        $items = collect(config('sidebar.navigation'))->flatMap(fn ($group) => $group['children']);
        $gallery = $items->firstWhere('key', 'gallery');
        $this->assertSame('admin.gallery.index', $gallery['route']);
        $this->assertArrayNotHasKey('children', $gallery);
        foreach (['admin.albums.index', 'admin.photos.index', 'admin.videos.index'] as $route) {
            $this->assertFalse(Route::has($route));
        }
        $this->get(route('admin.gallery.index'))->assertOk()->assertSee('No matching media.')->assertSee('Add media');
        $this->get(route('admin.gallery.index', ['tab' => 'albums', 'lang' => 'vn']))->assertOk()->assertSee('Tạo album');
        $this->assertDatabaseCount('gallery_media', 0);
    }

    public function test_images_save_replace_download_and_validate_without_losing_stored_image(): void
    {
        Storage::fake('local');
        $data = ['title' => 'Beach wedding', 'type' => 'image', 'position' => 1, 'is_visible' => 1];
        $this->postJson(route('admin.gallery.save'), $data)->assertUnprocessable()->assertJsonValidationErrors('photo');
        $this->post(route('admin.gallery.save'), $data + ['photo' => $this->photo()])->assertSessionHasNoErrors();
        $media = GalleryMedia::firstOrFail();
        $first = $media->photo;
        Storage::disk('local')->assertExists($first);
        $this->get(route('admin.gallery.photo', $media))->assertOk()->assertHeader('content-type', 'image/png');
        $this->get(route('admin.gallery.download', $media))->assertOk()->assertDownload('beach-wedding.png');
        $this->postJson(route('admin.gallery.save'), array_replace($data, ['id' => $media->id, 'title' => '', 'photo' => $this->photo()]))->assertUnprocessable()->assertJsonValidationErrors('title');
        $this->assertSame($first, $media->fresh()->photo);
        $this->post(route('admin.gallery.save'), $data + ['id' => $media->id, 'photo' => $this->photo()])->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($first);
        Storage::disk('local')->assertExists($media->fresh()->photo);
        $this->post(route('admin.gallery.save'), array_replace($data, ['id' => $media->id, 'type' => 'video', 'video_url' => 'https://youtu.be/abcdefghijk']))->assertSessionHasNoErrors();
        $this->assertNull($media->fresh()->photo);
        $this->get(route('admin.gallery.preview', $media))->assertOk()->assertSee('https://www.youtube-nocookie.com/embed/abcdefghijk', false);
    }

    public function test_album_assignment_filtering_and_deletion_keep_media(): void
    {
        $data = ['title' => 'Pre-wedding', 'position' => 2, 'is_visible' => 1, 'description' => 'Our memories'];
        $this->post(route('admin.gallery.albums.save'), $data)->assertSessionHasNoErrors();
        $album = GalleryAlbum::firstOrFail();
        $one = GalleryMedia::create(['title' => 'Video one', 'description' => 'Beach memory', 'type' => 'video', 'video_url' => 'https://example.com/movie.mp4']);
        GalleryMedia::create(['title' => 'Unrelated', 'type' => 'video', 'video_url' => 'https://example.com/other.mp4']);
        $this->post(route('admin.gallery.bulk'), ['ids' => [$one->id], 'action' => 'album', 'album_id' => $album->id])->assertSessionHasNoErrors();
        $this->assertSame($album->id, $one->fresh()->album_id);
        $this->get(route('admin.gallery.index', ['q' => 'Beach', 'album' => $album->id, 'tab' => 'video', 'view' => 'list']))->assertOk()->assertSee('Video one')->assertDontSee('Unrelated');
        $this->get(route('admin.gallery.index', ['tab' => 'albums', 'edit_album' => $album->id]))->assertOk()->assertSee('Pre-wedding');
        $this->delete(route('admin.gallery.albums.destroy', $album))->assertSessionHas('success');
        $this->assertNull($one->fresh()->album_id);
        $this->assertDatabaseCount('gallery_media', 2);
    }

    public function test_bulk_visibility_and_delete_only_affect_selected_media(): void
    {
        Storage::fake('local');
        $this->post(route('admin.gallery.save'), ['title' => 'Photo one', 'type' => 'image', 'position' => 0, 'is_visible' => 1, 'photo' => $this->photo()])->assertSessionHasNoErrors();
        $one = GalleryMedia::firstOrFail();
        $two = GalleryMedia::create(['title' => 'Video two', 'type' => 'video', 'video_url' => 'https://example.com/video.mp4']);
        $this->post(route('admin.gallery.bulk'), ['action' => 'hide', 'ids' => [$one->id]])->assertSessionHasNoErrors();
        $this->assertFalse($one->fresh()->is_visible);
        $this->assertTrue($two->fresh()->is_visible);
        $this->postJson(route('admin.gallery.bulk'), ['action' => 'delete', 'ids' => [$one->id, 999]])->assertUnprocessable()->assertJsonValidationErrors('ids.1');
        $this->assertDatabaseCount('gallery_media', 2);
        $this->post(route('admin.gallery.bulk'), ['action' => 'delete', 'ids' => [$one->id]])->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($one->photo);
        $this->assertDatabaseHas('gallery_media', ['id' => $two->id]);
        $this->postJson(route('admin.gallery.save'), ['title' => 'Bad video', 'type' => 'video', 'video_url' => 'javascript:alert(1)', 'position' => 0, 'is_visible' => 1])->assertUnprocessable()->assertJsonValidationErrors('video_url');
        $this->get(route('admin.gallery.index', ['selected' => 999]))->assertNotFound();
    }

    public function test_selected_media_download_as_zip_without_touching_other_files(): void
    {
        Storage::fake('local');
        $this->post(route('admin.gallery.save'), ['title' => 'Selected photo', 'type' => 'image', 'position' => 0, 'is_visible' => 1, 'photo' => $this->photo()])->assertSessionHasNoErrors();
        $photo = GalleryMedia::firstOrFail();
        $video = GalleryMedia::create(['title' => 'Selected video', 'type' => 'video', 'video_url' => 'https://example.com/video.mp4']);
        $other = GalleryMedia::create(['title' => 'Other video', 'type' => 'video', 'video_url' => 'https://example.com/other.mp4']);
        $response = $this->post(route('admin.gallery.download-selected'), ['ids' => [$photo->id, $video->id]])->assertOk()->assertDownload('wedding-gallery.zip');
        $path = $response->baseResponse->getFile()->getPathname();
        try {
            $zip = new \ZipArchive;
            $this->assertTrue($zip->open($path));
            $this->assertSame(2, $zip->numFiles);
            $this->assertNotFalse($zip->getFromName($photo->id.'-selected-photo.png'));
            $this->assertStringContainsString($video->video_url, $zip->getFromName($video->id.'-selected-video.txt'));
            $this->assertFalse($zip->getFromName($other->id.'-other-video.txt'));
            $zip->close();
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
        Storage::disk('local')->assertExists($photo->photo);
        $this->postJson(route('admin.gallery.download-selected'), ['ids' => [999]])->assertUnprocessable()->assertJsonValidationErrors('ids.0');
        $this->get(route('admin.gallery.index', ['selected' => 0]))->assertOk()->assertDontSee('data-gallery-form', false);
    }
}
