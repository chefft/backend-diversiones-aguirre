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
    Schema::create('galleries_360', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('image_path'); // Ruta a la imagen equirectangular
        $table->json('hotspots')->nullable(); // Puntos de interacción (coordenadas)
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galleries_360');
    }
};
