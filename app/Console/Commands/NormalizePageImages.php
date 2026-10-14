<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\SiteSetting;
use App\Support\PublicImagePath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NormalizePageImages extends Command
{
    protected $signature = 'site:normalize-images';

    protected $description = 'Give imported public images a real extension so admin previews and web servers recognize them';

    public function handle(): int
    {
        $changed = 0;
        DB::transaction(function () use (&$changed): void {
            Page::query()->chunkById(100, function ($pages) use (&$changed): void {
                foreach ($pages as $page) {
                    $blocks = PublicImagePath::replacePaths($page->blocks ?? []);
                    if ($blocks !== ($page->blocks ?? [])) {
                        $page->update(['blocks' => $blocks]);
                        $changed++;
                    }
                }
            });

            SiteSetting::query()->whereNotNull('header_logo')->each(function ($settings) use (&$changed): void {
                $path = PublicImagePath::withExtension($settings->header_logo);
                if ($path !== $settings->header_logo) {
                    $settings->update(['header_logo' => $path]);
                    $changed++;
                }
            });
        });

        // Include files no longer referenced by published pages, without changing any records.
        $copied = 0;
        foreach (Storage::disk('public')->files('page-blocks/live') as $path) {
            if (pathinfo($path, PATHINFO_EXTENSION) === '') {
                PublicImagePath::withExtension($path);
                $copied++;
            }
        }

        $this->components->info("Updated {$changed} records and checked {$copied} imported image files.");

        return self::SUCCESS;
    }
}
