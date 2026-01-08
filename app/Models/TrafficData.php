<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrafficData extends Model
{
    protected $table = 'traffic_data';

    protected $fillable = ['date', 'time', 'road', 'level_of_service', 'weather'];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get formatted datetime for display
     */
    public function getFormattedDateTimeAttribute()
    {
        return $this->date->format('M d, Y') . ' at ' . $this->time;
    }
}