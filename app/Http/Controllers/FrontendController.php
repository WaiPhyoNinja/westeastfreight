<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\FrontendContentController;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FrontendController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $slug = FrontendContentController::slug($request->path());
        $known = preg_match('#^my-orders/[0-9]+$#', $slug) || in_array($slug, ['login', 'reset-password', 'my-account', 'my-orders', 'profile', 'orders', 'request-quote'], true);
        $published = Page::published()->where(function ($query) use ($slug) {
            $query->where('slug', $slug);
            if (preg_match('#^(services|solutions)/(.+)$#', $slug, $matches)) {
                $query->orWhere(fn ($query) => $query->where('page_type', 'service')->where('slug', $matches[2]));
            }
        })->exists();

        $linkedService = ! $published && app(FrontendContentController::class)->linkedServicePage($request->path()) !== null;

        return response()->view('frontend', [], $known || $published || $linkedService ? 200 : 404);
    }
}
