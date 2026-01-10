<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrafficArchive extends Model
{
    protected $fillable = [
        'archive_id',
        'date',
        'time',
        'gil_fernando_los',
        'sumulong_los',
        'status',
        'created_by'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}