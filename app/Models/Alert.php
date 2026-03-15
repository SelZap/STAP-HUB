<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $primaryKey = 'alert_id';

    public $timestamps = false;

    protected $fillable = [
        'node_id',
        'camera_id',
        'type',
        'severity',
        'message',
        'is_resolved',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'is_resolved'  => 'boolean',
            'triggered_at' => 'datetime',
            'resolved_at'  => 'datetime',
        ];
    }

    // Relationships
    public function node(): BelongsTo
    {
        return $this->belongsTo(StapNode::class, 'node_id', 'node_id');
    }

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class, 'camera_id', 'camera_id');
    }
}