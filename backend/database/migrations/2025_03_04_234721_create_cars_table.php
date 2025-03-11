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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
            ->references('id')->on('users')
            ->onDelete('cascade');
            $table->string('car_type', 30);
            $table->string('license_plate', 20)->unique();
            $table->string('manufacturer', 100);
            $table->string('model', 100);
            $table->string('fuel_type', 50);
            $table->float('standard_consumption')->default(0);
            $table->integer('capacity');
            $table->integer('fuel_tank_capacity')->default(39);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
