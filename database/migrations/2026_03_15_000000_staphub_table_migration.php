<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // TABLE: admins
        // ============================================================
        Schema::create('admins', function (Blueprint $table) {
            $table->increments('admin_id');
            $table->string('admin_name', 100);
            $table->string('email', 150)->unique();
            $table->string('password_hash', 255);
            $table->boolean('is_superuser')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('last_login')->nullable();
        });

        // ============================================================
        // TABLE: stap_nodes
        // ============================================================
        Schema::create('stap_nodes', function (Blueprint $table) {
            $table->increments('node_id');
            $table->string('node_name', 100);
            $table->string('location_label', 150);
            $table->string('api_key', 255)->unique();
            $table->timestamp('last_heartbeat')->nullable();
            $table->enum('status', ['online', 'offline', 'error'])->default('offline');
            $table->timestamp('registered_at')->useCurrent();
        });

        // ============================================================
        // TABLE: cameras
        // ============================================================
        Schema::create('cameras', function (Blueprint $table) {
            $table->increments('camera_id');
            $table->unsignedInteger('node_id');
            $table->unsignedTinyInteger('usb_index');
            $table->string('label', 100);
            $table->string('direction', 50)->nullable();
            $table->enum('status', ['active', 'inactive', 'error'])->default('active');
            $table->timestamp('registered_at')->useCurrent();

            $table->foreign('node_id')
                  ->references('node_id')->on('stap_nodes')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->index('node_id', 'idx_cameras_node_id');
        });

        // ============================================================
        // TABLE: admin_activity_logs
        // ============================================================
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->increments('log_id');
            $table->unsignedInteger('admin_id');
            $table->string('action_type', 50);
            $table->string('target_type', 50)->nullable();
            $table->unsignedInteger('target_id')->nullable();
            $table->string('target_label', 100)->nullable();
            $table->text('details')->nullable();
            $table->timestamp('performed_at')->useCurrent();

            $table->foreign('admin_id')
                  ->references('admin_id')->on('admins')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->index('admin_id', 'idx_logs_admin_id');
            $table->index('performed_at', 'idx_logs_performed_at');
        });

        // ============================================================
        // TABLE: traffic_snapshots
        // ============================================================
        Schema::create('traffic_snapshots', function (Blueprint $table) {
            $table->increments('snapshot_id');
            $table->unsignedInteger('camera_id');
            $table->unsignedSmallInteger('vehicle_count')->default(0);
            $table->unsignedSmallInteger('cars')->default(0);
            $table->unsignedSmallInteger('trucks')->default(0);
            $table->unsignedSmallInteger('motorcycles')->default(0);
            $table->unsignedSmallInteger('buses')->default(0);
            $table->unsignedSmallInteger('emergency_vehicles')->default(0);
            $table->enum('congestion_level', ['free_flow', 'moderate', 'heavy', 'severe'])->default('free_flow');
            $table->string('image_url', 500)->nullable();
            $table->string('video_url', 500)->nullable();
            $table->timestamp('captured_at')->useCurrent();

            $table->foreign('camera_id')
                  ->references('camera_id')->on('cameras')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->index('camera_id', 'idx_snapshots_camera_id');
            $table->index('captured_at', 'idx_snapshots_captured_at');
        });

        // ============================================================
        // TABLE: traffic_lights
        // ============================================================
        Schema::create('traffic_lights', function (Blueprint $table) {
            $table->increments('light_id');
            $table->unsignedInteger('node_id');
            $table->string('location_label', 150);
            $table->enum('current_state', ['red', 'yellow', 'green'])->default('red');
            $table->enum('mode', ['auto', 'manual', 'hazard'])->default('auto');
            $table->unsignedSmallInteger('green_duration')->nullable();
            $table->unsignedSmallInteger('red_duration')->nullable();
            $table->timestamp('last_updated')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('node_id')
                  ->references('node_id')->on('stap_nodes')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->index('node_id', 'idx_lights_node_id');
        });

        // ============================================================
        // TABLE: weather_logs
        // ============================================================
        Schema::create('weather_logs', function (Blueprint $table) {
            $table->increments('weather_id');
            $table->unsignedInteger('node_id');
            $table->enum('rain_intensity', ['none', 'light', 'moderate', 'heavy'])->default('none');
            $table->timestamp('recorded_at')->useCurrent();

            $table->foreign('node_id')
                  ->references('node_id')->on('stap_nodes')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->index('node_id', 'idx_weather_node_id');
            $table->index('recorded_at', 'idx_weather_recorded_at');
        });

        // ============================================================
        // TABLE: alerts
        // ============================================================
        Schema::create('alerts', function (Blueprint $table) {
            $table->increments('alert_id');
            $table->unsignedInteger('node_id');
            $table->unsignedInteger('camera_id')->nullable();
            $table->string('type', 50);
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->text('message');
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('triggered_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();

            $table->foreign('node_id')
                  ->references('node_id')->on('stap_nodes')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->foreign('camera_id')
                  ->references('camera_id')->on('cameras')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->index('node_id', 'idx_alerts_node_id');
            $table->index('camera_id', 'idx_alerts_camera_id');
            $table->index('is_resolved', 'idx_alerts_is_resolved');
            $table->index('triggered_at', 'idx_alerts_triggered_at');
        });

        // ============================================================
        // TABLE: footage_requests
        // ============================================================
        Schema::create('footage_requests', function (Blueprint $table) {
            $table->increments('request_id');
            $table->unsignedInteger('camera_id');
            $table->string('requester_email', 150);
            $table->string('requester_contact', 50);
            $table->enum('request_nature', ['academic', 'personal', 'legal', 'media', 'other']);
            $table->date('footage_date');
            $table->time('footage_time_start');
            $table->time('footage_time_end');
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected'])->default('pending');
            $table->unsignedInteger('handled_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('camera_id')
                  ->references('camera_id')->on('cameras')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->foreign('handled_by')
                  ->references('admin_id')->on('admins')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->index('camera_id', 'idx_requests_camera_id');
            $table->index('handled_by', 'idx_requests_handled_by');
            $table->index('status', 'idx_requests_status');
        });

        // ============================================================
        // TABLE: request_messages
        // ============================================================
        Schema::create('request_messages', function (Blueprint $table) {
            $table->increments('message_id');
            $table->unsignedInteger('request_id');
            $table->enum('sender_type', ['admin', 'system'])->default('system');
            $table->unsignedInteger('admin_id')->nullable();
            $table->text('message');
            $table->text('requirement_list')->nullable();
            $table->timestamp('sent_at')->useCurrent();

            $table->foreign('request_id')
                  ->references('request_id')->on('footage_requests')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('admin_id')
                  ->references('admin_id')->on('admins')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->index('request_id', 'idx_messages_request_id');
            $table->index('admin_id', 'idx_messages_admin_id');
        });
    }

    public function down(): void
    {
        // Drop in reverse order to respect foreign key constraints
        Schema::dropIfExists('request_messages');
        Schema::dropIfExists('footage_requests');
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('weather_logs');
        Schema::dropIfExists('traffic_lights');
        Schema::dropIfExists('traffic_snapshots');
        Schema::dropIfExists('admin_activity_logs');
        Schema::dropIfExists('cameras');
        Schema::dropIfExists('stap_nodes');
        Schema::dropIfExists('admins');
    }
};
