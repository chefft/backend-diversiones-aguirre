<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'game_id',
        'start_date',
        'end_date',
        'total_price',
        'status',
    ];

    /**
     * Relación: La reserva pertenece a un usuario (Cliente).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: La reserva pertenece a un juego específico.
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}