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
        Schema::create('location_purpose', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')
                  ->references('id')->on('locations')
                  ->onDelete('cascade');
            $table->unsignedBigInteger('travel_purpose_id');
            $table->foreign('travel_purpose_id')
            ->references('id')->on('travel_purpose_dictionaries')
            ->onDelete('cascade');
            $table->unique(['location_id','travel_purpose_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_purpose');
    }
};
