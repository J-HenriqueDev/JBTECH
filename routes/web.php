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
use App\Http\Controllers\ClientesController;

Route::get('/get-reviews', [ReviewController::class, 'getReviews']);
Route::get('buscar-cep', [ClientesController::class, 'buscarCep'])->name('buscar_cep');
Route::get('/consulta-cnpj/{cnpj}', [ClientesController::class, 'consultaCnpj']);



// Main Page Route
Route::get('/', [Landing::class, 'index'])->name('front-pages-landing');
Route::get('/page-2', [Page2::class, 'index'])->name('pages-page-2');
Route::prefix('dashboard')->group(function () {
  Route::get('/clientes', [ClientesController::class, 'index'])->name('clientes');
});

// locale
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');

// Middleware for authenticated users
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [HomePage::class, 'index'])->name('dashboard');
});
