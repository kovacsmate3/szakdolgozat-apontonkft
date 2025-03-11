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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_id');
            $table->foreign('car_id')
            ->references('id')->on('cars')
            ->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
            ->references('id')->on('users')
            ->onDelete('cascade');
            $table->unsignedBigInteger('start_location_id');
            $table->foreign('start_location_id')
                ->references('id')->on('locations')
                ->onDelete('cascade');
            $table->unsignedBigInteger('destination_location_id');
            $table->foreign('destination_location_id')
                ->references('id')->on('locations')
                ->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->float('planned_distance')->nullable();
            $table->float('actual_distance')->nullable();
            $table->integer('start_odometer')->nullable();
            $table->integer('end_odometer')->nullable();
            $table->time('planned_duration')->nullable();
            $table->time('actual_duration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
