<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::dropIfExists('announcements');
    Schema::create('announcements', function (Blueprint $table) {
        $table->unsignedInteger('announcement_id')->autoIncrement();
        
        // unsignedInteger → matches admins.admin_id
        $table->unsignedInteger('created_by');
        $table->foreign('created_by')->references('admin_id')->on('admins')->onDelete('cascade');
        
        // unsignedBigInteger → matches incident_reports.incident_id (bigIncrements)
        $table->unsignedBigInteger('incident_report_id')->nullable();
        $table->foreign('incident_report_id')->references('incident_id')->on('incident_reports')->nullOnDelete();
        
        $table->string('title');
        $table->enum('type', ['general', 'incident', 'weather', 'maintenance', 'emergency'])->default('general');
        $table->text('content');
        $table->boolean('is_active')->default(true);
        $table->timestamp('expires_at')->nullable();
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};