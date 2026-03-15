<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StapNode extends Model
{
    protected $table = 'stap_nodes';
    protected $primaryKey = 'node_id';

    public $timestamps = false;

    protected $fillable = [
        'node_name',
        'location_label',
        'api_key',
        'last_heartbeat',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'last_heartbeat' => 'datetime',
            'registered_at'  => 'datetime',
        ];
    }

    // Relationships
    public function cameras(): HasMany
    {
        return $this->hasMany(Camera::class, 'node_id', 'node_id');
    }

    public function trafficLights(): HasMany
    {
        return $this->hasMany(TrafficLight::class, 'node_id', 'node_id');
    }

    public function weatherLogs(): HasMany
    {
        return $this->hasMany(WeatherLog::class, 'node_id', 'node_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'node_id', 'node_id');
    }
}