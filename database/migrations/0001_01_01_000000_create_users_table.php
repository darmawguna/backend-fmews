<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->enum('role', ['administrator', 'petugas'])->default('petugas');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->timestamps();

            // Index bisa didefinisikan di akhir untuk kerapian
            $table->index(['role', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // down() method Anda sudah benar, namun lebih aman untuk
        // membungkusnya dalam Schema::table()
        Schema::table('users', function (Blueprint $table) {
            // Urutan drop: index dan foreign key dulu, baru kolom.
            $table->dropForeign(['created_by']);
            $table->dropIndex(['users_role_is_active_index']); // Nama index default
            $table->dropColumn(['role', 'is_active', 'created_by']);
        });

        // Alternatif jika ingin menghapus seluruh tabel saat rollback
        // Schema::dropIfExists('users');
    }
};