<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrafficLight extends Model
{
    protected $table = 'traffic_lights';
    protected $primaryKey = 'light_id';

    public $timestamps = false;

    protected $fillable = [
        'node_id',
        'location_label',
        'current_state',
        'mode',
        'green_duration',
        'red_duration',
    ];

    protected function casts(): array
    {
        return [
            'last_updated' => 'datetime',
        ];
    }

    // Relationships
    public function node(): BelongsTo
    {
        return $this->belongsTo(StapNode::class, 'node_id', 'node_id');
    }
}