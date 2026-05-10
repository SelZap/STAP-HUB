<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FootageRequest extends Model
{
    protected $primaryKey = 'request_id';

    protected $fillable = [
        'camera_id',
        'requester_name',
        'requester_organization',
        'requester_address',
        'requester_email',
        'requester_contact',
        'request_nature',
        'other_reason',
        'footage_date',
        'footage_date_start',
        'footage_date_end',
        'footage_time_start',
        'footage_time_end',
        'incident_date',
        'incident_time',
        'names_involved',
        'incident_description',
        'status',
    ];

    protected $casts = [
        'camera_id'          => 'integer',
        'footage_date'       => 'date',
        'footage_date_start' => 'date',
        'footage_date_end'   => 'date',
        'incident_date'      => 'date',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
    ];

    // -------------------------
    // Relationships
    // -------------------------

    public function messages()
    {
        return $this->hasMany(RequestMessage::class, 'request_id', 'request_id');
    }
}