<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrafficSnapshot extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'snapshot_id';

    protected $fillable = [
        'camera_id',
        'cars',
        'trucks',
        'motorcycles',
        'mini_bus',
        'ambulance',
        'fire_truck',
        'tricycle',
        'jeepney',
        'congestion',
        'image_url',
        'video_url',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * LOS label map: congestion enum → full description
     */
    public static array $losLabels = [
        'A' => 'Free Flow',
        'B' => 'Near Free Flow',
        'C' => 'Stable',
        'D' => 'Approaching Unstable',
        'E' => 'Unstable',
        'F' => 'Forced Flow',
    ];

    /**
     * Total vehicle count across all types.
     */
    public function getTotalVehiclesAttribute(): int
    {
        return $this->cars
            + $this->trucks
            + $this->motorcycles
            + $this->mini_bus
            + $this->ambulance
            + $this->fire_truck
            + $this->tricycle
            + $this->jeepney;
    }

    /**
     * Human-readable LOS description.
     */
    public function getLosLabelAttribute(): string
    {
        return self::$losLabels[$this->congestion] ?? 'Unknown';
    }

    // -------------------------
    // Relationships
    // -------------------------

    public function camera()
    {
        return $this->belongsTo(Camera::class, 'camera_id', 'camera_id');
    }
}