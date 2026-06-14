<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class PublicStorageUrl
{
    public static function fromPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $url = Storage::disk('public')->url($path);

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return rtrim(config('app.url'), '/').'/'.ltrim($url, '/');
    }
}
