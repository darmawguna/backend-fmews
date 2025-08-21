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
        Schema::create('fasilitas_publik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kategori')->constrained('kategori_fasilitas')->onDelete('cascade');
            $table->string('nama_fasilitas', 255);
            $table->text('alamat');
            $table->decimal('latitude', 10, 8); // Presisi untuk koordinat
            $table->decimal('longitude', 11, 8); // Presisi untuk koordinat
            $table->text('deskripsi')->nullable();
            $table->string('status_operasional')->default('Beroperasi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fasilitas_publik');
    }

};
