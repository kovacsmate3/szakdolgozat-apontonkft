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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id')->unique()->nullable();
            $table->foreign('location_id')
            ->references('id')->on('locations')
            ->onDelete('cascade');
            $table->string('country', 100)->default('Magyarország');
            $table->integer('postalcode');
            $table->string('city', 100)->default('Budapest');
            $table->string('road_name', 100);
            $table->string('public_space_type', 50);
            $table->string('building_number', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
