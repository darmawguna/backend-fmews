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
        Schema::create('shelters', function (Blueprint $table) {
            $table->id();
            $table->string('nama_shelter', 255);
            $table->text('alamat');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->unsignedInteger('kapasitas_maksimum');
            $table->unsignedInteger('ketersediaan_saat_ini')->default(0);
            $table->enum('status', ['Buka', 'Penuh', 'Tutup'])->default('Buka');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shelters');
    }

};
