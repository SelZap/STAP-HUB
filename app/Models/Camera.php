<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Camera extends Model
{
    protected $primaryKey = 'camera_id';

    public $timestamps = false;

    protected $fillable = [
        'node_id',
        'usb_index',
        'label',
        'direction',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
        ];
    }

    // Relationships
    public function node(): BelongsTo
    {
        return $this->belongsTo(StapNode::class, 'node_id', 'node_id');
    }

    public function trafficSnapshots(): HasMany
    {
        return $this->hasMany(TrafficSnapshot::class, 'camera_id', 'camera_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'camera_id', 'camera_id');
    }

    public function footageRequests(): HasMany
    {
        return $this->hasMany(FootageRequest::class, 'camera_id', 'camera_id');
    }
}