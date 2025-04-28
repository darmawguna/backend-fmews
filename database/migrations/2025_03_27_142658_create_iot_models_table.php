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
        Schema::create('iot_waterlevel_devices', function (Blueprint $table) {
            $table->id();
            $table->string("device_id");
            $table->string("device_name");
            $table->float("latitude");
            $table->float("longitude");
            $table->string("api_token")->nullable();
            $table->float("sensor_height")->nullable();
            $table->float("warning_level")->nullable();
            $table->float("danger_level")->nullable();
            $table->enum("status", ["active", "deactive",]);
            $table->longText('public_key')->nullable();
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
