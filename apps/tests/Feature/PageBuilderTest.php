<?php

namespace Tests\Feature;

use App\Models\PageBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PageBuilderTest extends TestCase
{
    use RefreshDatabase;

    private function block(array $overrides = []): array
    {
        return array_replace(['id' => (string) Str::uuid(), 'type' => 'heading', 'name' => 'Title block', 'title' => 'Our special day', 'text' => '', 'url' => '', 'button_label' => 'Learn more', 'align' => 'center', 'background' => '#FFF9FB', 'padding' => 32, 'height' => 0, 'desktop' => true, 'tablet' => true, 'mobile' => true, 'animation' => 'none', 'duration' => 600], $overrides);
    }

    public function test_screen_and_unsaved_preview_do_not_create_data(): void
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->get(route('admin.page-builder.index'))->assertOk()->assertSee('Save draft')->assertSee('data-pb-canvas', false)->assertSee('data-pb-add="hero"', false);
        $blocks = [$this->block(['title' => '<script>alert(1)</script>', 'mobile' => false])];
        $html = $this->postJson(route('admin.page-builder.preview'), ['blocks' => $blocks])->assertOk()->assertHeader('Cache-Control', 'no-store, private')->json('html');
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('builder-hide-mobile', $html);
        $this->assertDatabaseCount('page_builders', 0);
        $this->assertDatabaseCount('content_pages', 0);
    }

    public function test_draft_order_and_published_content_are_stored_separately(): void
    {
        $first = $this->block(['title' => 'First heading']);
        $second = $this->block(['title' => 'Second heading']);
        $this->putJson(route('admin.page-builder.save'), ['blocks' => [$first, $second], 'revision' => 0, 'action' => 'draft'])->assertOk()->assertJsonPath('revision', 1);
        $builder = PageBuilder::firstOrFail();
        $this->assertNull($builder->published);
        $this->putJson(route('admin.page-builder.save'), ['blocks' => [$second, $first], 'revision' => 1, 'action' => 'publish'])->assertOk()->assertJsonPath('revision', 2);
        $this->assertSame($second['id'], $builder->fresh()->published[0]['id']);
        $this->assertNotNull($builder->fresh()->published_at);
        $this->putJson(route('admin.page-builder.save'), ['blocks' => [$this->block(['title' => 'Draft only'])], 'revision' => 2, 'action' => 'draft'])->assertOk();
        $this->get(route('admin.page-builder.show', ['version' => 'published']))->assertOk()->assertSee('First heading')->assertDontSee('Draft only');
        $this->get(route('admin.page-builder.show', ['version' => 'draft']))->assertOk()->assertSee('Draft only')->assertDontSee('First heading');
        $this->assertDatabaseCount('page_builders', 1);
        $this->putJson(route('admin.page-builder.save'), ['blocks' => [], 'revision' => 3, 'action' => 'publish'])->assertOk();
        $this->assertSame([], $builder->fresh()->published);
    }

    public function test_invalid_blocks_and_stale_saves_cannot_overwrite_data(): void
    {
        $block = $this->block();
        $this->putJson(route('admin.page-builder.save'), ['blocks' => [$block], 'revision' => 0, 'action' => 'draft'])->assertOk();
        $this->putJson(route('admin.page-builder.save'), ['blocks' => [], 'revision' => 0, 'action' => 'publish'])->assertConflict();
        $this->assertCount(1, PageBuilder::firstOrFail()->draft);
        $invalid = $this->block(['type' => 'html', 'url' => 'javascript:alert(1)', 'background' => 'red;display:none', 'align' => 'bad', 'padding' => 999, 'mobile' => 'bad']);
        $this->putJson(route('admin.page-builder.save'), ['blocks' => [$invalid], 'revision' => 1, 'action' => 'publish'])->assertUnprocessable()->assertJsonValidationErrors(['blocks.0.type', 'blocks.0.url', 'blocks.0.background', 'blocks.0.align', 'blocks.0.padding', 'blocks.0.mobile']);
        $this->postJson(route('admin.page-builder.preview'), ['blocks' => [$block, $block]])->assertUnprocessable()->assertJsonValidationErrors('blocks.0.id');
        $this->assertNull(PageBuilder::firstOrFail()->published);
    }

    public function test_every_supported_block_can_be_previewed(): void
    {
        $blocks = [];
        foreach (array_keys(config('page_builder.blocks')) as $type) {
            $blocks[] = $this->block(['type' => $type, 'name' => $type, 'title' => 'Sample heading', 'text' => 'Sample content', 'url' => in_array($type, ['hero', 'image', 'button']) ? 'https://example.com/photo.jpg' : '']);
        }
        $this->postJson(route('admin.page-builder.preview'), ['blocks' => $blocks])->assertOk();
        $this->assertDatabaseCount('page_builders', 0);
    }
}
