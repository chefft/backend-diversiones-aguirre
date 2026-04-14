<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gallery360;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GalleryController extends Controller
{
    public function index()
    {
        $galleries = Gallery360::where('is_active', true)
            ->get()
            ->map(function (Gallery360 $gallery) {
                $payload = $gallery->toArray();
                $payload['image_url'] = $this->resolveAssetUrl($gallery->image_path);

                return $payload;
            });

        return response()->json($galleries);
    }

    private function resolveAssetUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');

        if (Str::startsWith($normalizedPath, 'storage/')) {
            return url('/' . $normalizedPath);
        }

        return url(Storage::disk('public')->url($normalizedPath));
    }
}
