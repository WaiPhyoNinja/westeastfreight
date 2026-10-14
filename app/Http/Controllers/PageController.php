<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Http\Response;

class PageController extends Controller
{
    public function show(string $locale = 'en', string $slug = 'home'): Response
    {
        abort_unless(in_array($locale, ['en', 'my', 'th'], true), 404);

        $page = Page::published()->where('slug', $slug)->firstOrFail();

        $settings = SiteSetting::query()->first();

        return response()->view('site.page', compact('page', 'locale', 'settings'));
    }
}
