<?php

use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\QuoteRequestController;
use App\Http\Controllers\Api\WebsiteSubmissionController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\InvoicePrintController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/frontend')->group(function (): void {
    Route::get('/csrf', fn () => response()->json(['token' => csrf_token()])->header('Cache-Control', 'no-store'));
    Route::get('/auth/user', [CustomerAuthController::class, 'user']);
    Route::post('/auth/login', [CustomerAuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/auth/register', [CustomerAuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/auth/logout', [CustomerAuthController::class, 'logout']);
    Route::post('/auth/password/request', [CustomerAuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
    Route::post('/auth/password/reset', [CustomerAuthController::class, 'resetPassword'])->middleware('throttle:5,1');

    Route::post('/items/{collection}', [WebsiteSubmissionController::class, 'store'])
        ->whereIn('collection', ['contact_us', 'newsletter'])->middleware('throttle:5,1');

    Route::middleware('auth')->group(function (): void {
        Route::patch('/auth/profile', [CustomerAuthController::class, 'updateProfile'])->middleware('throttle:10,1');
        Route::post('/auth/password', [CustomerAuthController::class, 'updatePassword'])->middleware('throttle:5,1');
        Route::get('/items/quote_requests/{quote}', [QuoteRequestController::class, 'show'])->whereNumber('quote');
        Route::get('/items/quote_requests', [QuoteRequestController::class, 'index']);
        Route::post('/items/quote_requests', [QuoteRequestController::class, 'store'])->middleware('throttle:20,1');
        Route::patch('/items/quote_requests/{quote}', [QuoteRequestController::class, 'update'])->whereNumber('quote')->middleware('throttle:20,1');
    });
});

Route::get('/admin/invoices/{invoice}/print', InvoicePrintController::class)
    ->name('invoices.print');

Route::get('/preview/pages/{page}', function (\App\Models\Page $page) {
    abort_unless(auth()->user()?->can('pages.view'), 403);

    return response()->view('frontend', [
        'pagePreview' => app(\App\Http\Controllers\Api\FrontendContentController::class)->pageData($page),
    ])->header('Cache-Control', 'private, no-store')->header('X-Robots-Tag', 'noindex, nofollow');
})->name('pages.preview');

Route::get('/{path?}', FrontendController::class)
    ->where('path', '(?!(?:admin|api|livewire|storage|assets|build|up)(?:/|$)).*');
