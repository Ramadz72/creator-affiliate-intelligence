<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

use App\Http\Controllers\CreatorController;

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

use App\Http\Controllers\CreatorRateCardController;

Route::get(
    '/creators/{creator}/rate-cards/create',
    [CreatorRateCardController::class, 'create']
)
    ->middleware(['auth'])
    ->name('creators.rate-cards.create');

Route::post(
    '/creators/{creator}/rate-cards',
    [CreatorRateCardController::class, 'store']
)
    ->middleware(['auth'])
    ->name('creators.rate-cards.store');

Route::delete(
    '/creators/{creator}/rate-cards/{rateCard}',
    [CreatorRateCardController::class, 'destroy']
)
    ->middleware(['auth'])
    ->name('creators.rate-cards.destroy');

use App\Http\Controllers\CreatorAnalysisController;

Route::get('/creators/{creator}/analysis', [CreatorAnalysisController::class, 'show'])
    ->middleware(['auth'])
    ->name('creators.analysis');

Route::get('/creators/{creator}/analysis/history', [CreatorAnalysisController::class, 'history'])
    ->middleware(['auth'])
    ->name('creators.analysis.history');

Route::get('/creators/{creator}/analysis/history/{score}', [CreatorAnalysisController::class, 'historyDetail'])
    ->middleware(['auth'])
    ->name('creators.analysis.history.detail');

use App\Http\Controllers\CampaignController;

Route::resource('campaigns', CampaignController::class)
    ->middleware(['auth']);

use App\Http\Controllers\CreatorCampaignPerformanceController;

Route::get(
    '/campaigns/{campaign}/performance/create',
    [CreatorCampaignPerformanceController::class, 'create']
)
    ->middleware(['auth'])
    ->name('campaigns.performance.create');

Route::post(
    '/campaigns/{campaign}/performance',
    [CreatorCampaignPerformanceController::class, 'store']
)
    ->middleware(['auth'])
    ->name('campaigns.performance.store');

    Route::get(
    '/campaigns/{campaign}/performance/{performance}/edit',
    [CreatorCampaignPerformanceController::class, 'edit']
)
    ->middleware(['auth'])
    ->name('campaigns.performance.edit');

Route::put(
    '/campaigns/{campaign}/performance/{performance}',
    [CreatorCampaignPerformanceController::class, 'update']
)
    ->middleware(['auth'])
    ->name('campaigns.performance.update');

use App\Http\Controllers\AffiliateImportController;

Route::get('/imports', function () {
    return Inertia::render('imports/Index');
})
    ->middleware(['auth'])
    ->name('imports.index');

Route::post('/imports/affiliate', [AffiliateImportController::class, 'store'])
    ->middleware(['auth'])
    ->name('imports.affiliate.store');

use App\Http\Controllers\ImportHistoryController;

Route::get('/history', [ImportHistoryController::class, 'index'])
    ->middleware(['auth'])
    ->name('history.index');

use App\Http\Controllers\AffiliateController;

Route::get('/affiliates', [AffiliateController::class, 'index'])
    ->middleware(['auth'])
    ->name('affiliates.index');

use App\Http\Controllers\AffiliateDetailController;

Route::get('/affiliates/{affiliate}', [AffiliateDetailController::class, 'show'])
    ->middleware(['auth'])
    ->name('affiliates.show');

use App\Http\Controllers\HistoryController;

Route::get('/history', [HistoryController::class, 'index'])
    ->middleware(['auth'])
    ->name('history.index');

Route::get('/history/{batch}', [HistoryController::class, 'show'])
    ->middleware(['auth'])
    ->name('history.show');

Route::delete('/history/{batch}', [HistoryController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('history.destroy');