<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gallery360;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index()
    {
        // Retorna solo las galerías activas
        return response()->json(Gallery360::where('is_active', true)->get());
    }
}