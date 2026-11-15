<?php

use App\Http\Controllers\Admin\CoupleController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin', DashboardController::class)->name('admin.dashboard');

Route::put('/admin/couple', CoupleController::class.'@update')->name('admin.couple.update');
Route::get('/admin/couple/photos/{person}', CoupleController::class.'@photo')->name('admin.couple.photo');

Route::get('/admin/couple/qr/{person}', CoupleController::class.'@photo')->defaults('kind', 'bank_qr')->name('admin.couple.qr');
