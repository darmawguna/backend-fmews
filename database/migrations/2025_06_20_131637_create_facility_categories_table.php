<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_fasilitas', function (Blueprint $table) {
            $table->id(); // Primary Key (bigint, unsigned, auto-increment)
            $table->string('nama_kategori', 100);
            $table->text('deskripsi')->nullable();
            $table->timestamps(); // Membuat kolom created_at dan updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_fasilitas');
    }
};
