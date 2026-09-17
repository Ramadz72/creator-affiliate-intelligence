<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

use App\Http\Controllers\CreatorController;

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('/creators', [CreatorController::class, 'index'])
    ->middleware(['auth'])
    ->name('creators.index');

Route::get('/creators/create', [CreatorController::class, 'create'])
    ->middleware(['auth'])
    ->name('creators.create');

Route::post('/creators', [CreatorController::class, 'store'])
    ->middleware(['auth'])
    ->name('creators.store');

Route::get('/creators/{creator}', [CreatorController::class, 'show'])
    ->middleware(['auth'])
    ->name('creators.show');

Route::get('/creators/{creator}/edit', [CreatorController::class, 'edit'])
    ->middleware(['auth'])
    ->name('creators.edit');

Route::put('/creators/{creator}', [CreatorController::class, 'update'])
    ->middleware(['auth'])
    ->name('creators.update');

Route::delete('/creators/{creator}', [CreatorController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('creators.destroy');

use App\Http\Controllers\CreatorContentController;

Route::get(
    '/creators/{creator}/contents/create',
    [CreatorContentController::class, 'create']
)
    ->middleware(['auth'])
    ->name('creators.contents.create');
    
Route::post(
    '/creators/{creator}/contents',
    [CreatorContentController::class, 'store']
)
    ->middleware(['auth'])
    ->name('creators.contents.store');

Route::delete(
    '/creators/{creator}/contents/{content}',
    [CreatorContentController::class, 'destroy']
)
    ->middleware(['auth'])
    ->name('creators.contents.destroy');