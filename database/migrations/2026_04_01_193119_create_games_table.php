<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up(): void
{
    Schema::create('games', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description')->nullable();
        $table->decimal('price_per_hour', 10, 2);
        $table->string('model_3d_path')->nullable(); // Ruta al .glb
        $table->string('unity_build_url')->nullable(); // Link al WebGL
        $table->string('image_cover')->nullable();
        $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
