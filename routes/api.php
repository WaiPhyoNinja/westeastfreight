<?php

use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\FrontendContentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/pages', [ContentController::class, 'index']);
    Route::get('/pages/{slug}', [ContentController::class, 'show']);
    Route::get('/site-settings', [ContentController::class, 'settings']);
});

Route::get('/frontend/items/{collection}', [FrontendContentController::class, 'index'])
    ->whereIn('collection', ['pages', 'services', 'header', 'footer', 'locations', 'menu', 'social_icons', 'quote_country']);
