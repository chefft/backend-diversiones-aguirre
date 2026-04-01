<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_per_hour',
        'model_3d_path',
        'unity_build_url',
        'image_cover',
        'status',
    ];

    /**
     * Relación: Un juego puede tener muchas reservas.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}