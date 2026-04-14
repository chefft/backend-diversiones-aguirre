<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->index(['game_id', 'status', 'start_date', 'end_date'], 'reservations_game_status_time_idx');
            $table->index(['start_date', 'end_date'], 'reservations_time_idx');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex('reservations_game_status_time_idx');
            $table->dropIndex('reservations_time_idx');
        });
    }
};
