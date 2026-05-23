<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->string('vehicle_type', 500)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->enum('vehicle_type', [
                'car','truck','motorcycle','bus','mini_bus',
                'tricycle','jeepney','ambulance','fire_truck','emergency_vehicle'
            ])->nullable()->change();
        });
    }
};