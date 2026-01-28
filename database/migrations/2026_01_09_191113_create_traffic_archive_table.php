// database/migrations/xxxx_xx_xx_create_traffic_archives_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traffic_archives', function (Blueprint $table) {
            $table->id();
            $table->string('archive_id', 50)->unique();
            $table->date('date');
            $table->time('time');
            $table->enum('gil_fernando_los', ['A', 'B', 'C', 'D', 'E', 'F']);
            $table->enum('sumulong_los', ['A', 'B', 'C', 'D', 'E', 'F']);
            $table->string('status', 20)->default('Completed');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index('date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traffic_archives');
    }
};