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
        Schema:: create('traffic_data', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('time');
            $table->string('road')->comment('e.g., Main St, Oak Ave');
            $table->enum('level_of_service', ['light', 'medium', 'heavy'])->comment('LOS:  Light, Medium, Heavy');
            $table->string('weather')->comment('e.g., Sunny, Rainy, Cloudy');
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index('date');
            $table->index('road');
            $table->index('level_of_service');
        });
    }

    /**
     * Reverse the migrations. 
     */
    public function down(): void
    {
        Schema:: dropIfExists('traffic_data');
    }
};