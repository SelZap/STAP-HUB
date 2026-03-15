<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrafficSnapshot extends Model
{
    protected $table = 'traffic_snapshots';
    protected $primaryKey = 'snapshot_id';

    public $timestamps = false;

    protected $fillable = [
        'camera_id',
        'vehicle_count',
        'cars',
        'trucks',
        'motorcycles',
        'buses',
        'emergency_vehicles',
        'congestion_level',
        'image_url',
        'video_url',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
        ];
    }

    // Relationships
    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class, 'camera_id', 'camera_id');
    }
}