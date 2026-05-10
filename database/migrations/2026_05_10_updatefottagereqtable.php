<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('footage_requests', function (Blueprint $table) {
            // INT UNSIGNED (matches original schema) → allow null for "All cameras"
            $table->unsignedInteger('camera_id')->nullable()->change();

            // Multi-date range support
            $table->date('footage_date')->nullable()->change();
            $table->date('footage_date_start')->nullable()->after('footage_date');
            $table->date('footage_date_end')->nullable()->after('footage_date_start');

            // "Other" nature reason
            $table->string('other_reason', 500)->nullable()->after('request_nature');
        });
    }

    public function down(): void
    {
        Schema::table('footage_requests', function (Blueprint $table) {
            $table->unsignedInteger('camera_id')->nullable(false)->change();
            $table->date('footage_date')->nullable(false)->change();
            $table->dropColumn(['footage_date_start', 'footage_date_end', 'other_reason']);
        });
    }
};