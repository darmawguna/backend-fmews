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
        Schema::create('iot_devices', function (Blueprint $table) {
            $table->id();
            $table->string("device_id");
            $table->string("device_name");
            $table->string("location")->nullable();
            $table->float("latitude");
            $table->float("longitude");
            $table->float("sensor_height")->nullable();
            $table->float("warning_level")->nullable();
            $table->float("danger_level")->nullable();
            $table->enum("status", ["active", "deactive", "pending",])->default("pending");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iot_models');
    }
};
