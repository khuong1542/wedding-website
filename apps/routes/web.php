<?php

use App\Http\Controllers\Admin\CoupleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OurStoryController;
use App\Http\Controllers\Admin\RegistryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin', DashboardController::class)->name('admin.dashboard');

Route::put('/admin/couple', CoupleController::class.'@update')->name('admin.couple.update');
Route::get('/admin/couple/photos/{person}', CoupleController::class.'@photo')->name('admin.couple.photo');

Route::get('/admin/couple/qr/{person}', CoupleController::class.'@photo')->defaults('kind', 'bank_qr')->name('admin.couple.qr');

Route::put('/admin/our-story', OurStoryController::class.'@update')->name('admin.our-story.update');
Route::get('/admin/our-story/photos/{milestone}', OurStoryController::class.'@photo')->name('admin.our-story.photo');

Route::patch('/admin/our-story/order', OurStoryController::class.'@reorder')->name('admin.our-story.order');

Route::put('/admin/registry', RegistryController::class.'@update')->name('admin.registry.update');
