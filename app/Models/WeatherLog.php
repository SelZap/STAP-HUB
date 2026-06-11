<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherLog extends Model
{
    protected $table = 'weather_logs';
    protected $primaryKey = 'weather_id';

    public $timestamps = false;

    protected $fillable = [
        'node_id',
        'rain_intensity',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
        ];
    }

    // Relationships
    public function node(): BelongsTo
    {
        return $this->belongsTo(StapNode::class, 'node_id', 'node_id');
    }
}