<?php

use App\Http\Controllers\Admin\ContentPageController;
use App\Http\Controllers\Admin\CoupleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvitationController;
use App\Http\Controllers\Admin\OurStoryController;
use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Admin\RegistryController;
use App\Http\Controllers\Admin\RsvpResponseController;
use App\Http\Controllers\Admin\WeddingEventController;
use App\Http\Middleware\AdminContext;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(AdminContext::class)->group(function () {
    Route::get('/admin', DashboardController::class)->defaults('module', 'overview')->name('admin.dashboard');

    $registerAdminPages = function (array $items) use (&$registerAdminPages): void {
        foreach ($items as $item) {
            if (! empty($item['children'])) {
                $registerAdminPages($item['children']);
            } elseif ($item['key'] !== 'overview') {
                $path = match ($item['key']) {
                    'wedding' => 'couple', default => $item['key']
                };
                $action = match ($item['key']) {
                    'wedding' => [CoupleController::class, 'index'],
                    'our-story' => [OurStoryController::class, 'index'],
                    'registry' => [RegistryController::class, 'index'],
                    'events' => [WeddingEventController::class, 'index'],
                    'pages' => [ContentPageController::class, 'index'],
                    'rsvp' => [RsvpResponseController::class, 'index'],
                    'invitations' => [InvitationController::class, 'index'],
                    'shared-events' => [WeddingEventController::class, 'shared'],
                    default => PlaceholderController::class,
                };
                Route::get('/admin/'.$path, $action)
                    ->defaults('module', $item['key'])
                    ->name($item['route']);
            }
        }
    };
    $registerAdminPages(config('sidebar.navigation', []));

    Route::put('/admin/couple', CoupleController::class.'@update')->name('admin.couple.update');
    Route::get('/admin/couple/photos/{person}', CoupleController::class.'@photo')->whereIn('person', ['groom', 'bride'])->name('admin.couple.photo');

    Route::get('/admin/couple/qr/{person}', CoupleController::class.'@photo')->defaults('kind', 'bank_qr')->whereIn('person', ['groom', 'bride'])->name('admin.couple.qr');

    Route::put('/admin/our-story', OurStoryController::class.'@update')->name('admin.our-story.update');
    Route::get('/admin/our-story/photos/{milestone}', OurStoryController::class.'@photo')->whereNumber('milestone')->name('admin.our-story.photo');

    Route::patch('/admin/our-story/order', OurStoryController::class.'@reorder')->name('admin.our-story.order');

    Route::put('/admin/registry', RegistryController::class.'@update')->name('admin.registry.update');

    Route::post('/admin/events', WeddingEventController::class.'@save')->name('admin.events.save');
    Route::patch('/admin/events/{event}/visibility', WeddingEventController::class.'@visibility')->whereNumber('event')->name('admin.events.visibility');
    Route::delete('/admin/events/{event}', WeddingEventController::class.'@destroy')->whereNumber('event')->name('admin.events.destroy');
    Route::get('/admin/events/{event}/photo', WeddingEventController::class.'@photo')->whereNumber('event')->name('admin.events.photo');

});

Route::post('/admin/shared-events', [WeddingEventController::class, 'save'])->middleware(AdminContext::class)->defaults('event_scope', 'shared')->name('admin.shared-events.save');

Route::middleware(AdminContext::class)->group(function () {
    Route::post('/admin/pages/preview', [ContentPageController::class, 'preview'])->name('admin.pages.preview');
    Route::post('/admin/pages', [ContentPageController::class, 'save'])->name('admin.pages.save');
    Route::patch('/admin/pages/{contentPage}/visibility', [ContentPageController::class, 'visibility'])->whereNumber('contentPage')->name('admin.pages.visibility');
    Route::delete('/admin/pages/{contentPage}', [ContentPageController::class, 'delete'])->whereNumber('contentPage')->name('admin.pages.delete');
    Route::get('/admin/pages/{contentPage}/photo', [ContentPageController::class, 'photo'])->whereNumber('contentPage')->name('admin.pages.photo');
});

Route::middleware(AdminContext::class)->group(function () {
    Route::put('/admin/rsvp/{response}', [RsvpResponseController::class, 'update'])->whereNumber('response')->name('admin.rsvp.update');
    Route::delete('/admin/rsvp/{response}', [RsvpResponseController::class, 'destroy'])->whereNumber('response')->name('admin.rsvp.destroy');
});

Route::middleware(AdminContext::class)->group(function () {
    Route::post('/admin/invitations/preview', [InvitationController::class, 'preview'])->name('admin.invitations.preview');
    Route::post('/admin/invitations', [InvitationController::class, 'save'])->name('admin.invitations.save');
    Route::post('/admin/invitations/{invitation}/duplicate', [InvitationController::class, 'duplicate'])->whereNumber('invitation')->name('admin.invitations.duplicate');
    Route::delete('/admin/invitations/{invitation}', [InvitationController::class, 'destroy'])->whereNumber('invitation')->name('admin.invitations.destroy');
    Route::get('/admin/invitations/{invitation}/photo', [InvitationController::class, 'photo'])->whereNumber('invitation')->name('admin.invitations.photo');
    Route::get('/admin/invitations/{invitation}/preview', [InvitationController::class, 'show'])->whereNumber('invitation')->name('admin.invitations.show');
});
