<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->bigIncrements('incident_id');
            $table->date('incident_date');
            $table->time('incident_time');
            $table->enum('environmental_condition', [
                'clear', 'cloudy', 'rainy', 'foggy', 'night'
            ]);
            $table->string('location_description', 500);
            $table->enum('vehicle_type', [
                'car', 'truck', 'motorcycle', 'bus', 'mini_bus',
                'tricycle', 'jeepney', 'ambulance', 'fire_truck', 'emergency_vehicle'
            ])->nullable();
            $table->unsignedTinyInteger('vehicle_count')->nullable();
            $table->boolean('people_hurt')->default(false);
            $table->unsignedTinyInteger('injured_count')->nullable();
            $table->text('description');
            $table->string('reporting_party_name');
            $table->string('reporter_email');
            $table->enum('status', ['pending', 'reviewed'])->default('pending');
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('reviewed_by')
                  ->references('admin_id')
                  ->on('admins')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->index('status', 'idx_incident_reports_status');
            $table->index('reviewed_by', 'idx_incident_reports_reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};