<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\Page2;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\pages\Landing;


use App\Http\Controllers\ReviewController;

Route::get('/get-reviews', [ReviewController::class, 'getReviews']);

// Main Page Route
Route::get('/', [Landing::class, 'index'])->name('front-pages-landing');
Route::get('/page-2', [Page2::class, 'index'])->name('pages-page-2');
Route::get('/dashboard', [HomePage::class, 'index'])->name('pages-home');



// locale
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');

// authentication
// Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
// Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('content.pages.pages-home');
    })->name('dashboard');
});
