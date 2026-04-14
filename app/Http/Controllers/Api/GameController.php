<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::where('status', 'active')
            ->get()
            ->map(fn (Game $game) => $this->transformGame($game));

        return response()->json($games);
    }

    public function show(string $slug)
    {
        $game = Game::where('slug', $slug)->firstOrFail();

        return response()->json($this->transformGame($game));
    }

    private function transformGame(Game $game): array
    {
        $payload = $game->toArray();
        $payload['model_3d_url'] = $this->resolveAssetUrl($game->model_3d_path);
        $payload['image_cover_url'] = $this->resolveAssetUrl($game->image_cover);

        return $payload;
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
