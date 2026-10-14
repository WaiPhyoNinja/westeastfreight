<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PublicImagePath
{
    private const EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/avif' => 'avif',
    ];

    /** Keep the original file so existing links remain valid. */
    public static function withExtension(string $path): string
    {
        if (! str_starts_with($path, 'page-blocks/live/') || pathinfo($path, PATHINFO_EXTENSION) !== '') {
            return $path;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            throw new RuntimeException("Missing public image: {$path}");
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($disk->path($path));
        $extension = self::EXTENSIONS[$mime] ?? throw new RuntimeException("Unsupported image type for {$path}: {$mime}");
        $newPath = "{$path}.{$extension}";

        if (! $disk->exists($newPath) && ! $disk->copy($path, $newPath)) {
            throw new RuntimeException("Could not copy public image: {$path}");
        }

        return $newPath;
    }

    public static function replacePaths(mixed $value): mixed
    {
        if (is_string($value)) {
            return self::withExtension($value);
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = self::replacePaths($item);
            }
        }

        return $value;
    }
}
