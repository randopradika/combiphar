<?php

use App\Http\Controllers\PageController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/' . config('app.locale')));

Route::prefix('{locale}')
    ->whereIn('locale', SetLocale::SUPPORTED)
    ->middleware(SetLocale::class)
    ->group(function () {
        Route::get('/', [PageController::class, 'home'])->name('home');
        Route::get('about', [PageController::class, 'about'])->name('about');
        Route::get('products', [PageController::class, 'products'])->name('products');
        Route::get('csr', [PageController::class, 'csr'])->name('csr');
        Route::get('news', [PageController::class, 'news'])->name('news');
        Route::get('news/{slug}', [PageController::class, 'newsShow'])->name('news.show');
        Route::get('investor', [PageController::class, 'investor'])->name('investor');
        Route::get('contact', [PageController::class, 'contact'])->name('contact');
        Route::post('contact', [PageController::class, 'contactSubmit'])->name('contact.submit');
    });