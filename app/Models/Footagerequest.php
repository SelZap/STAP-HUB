<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FootageRequest extends Model
{
    protected $primaryKey = 'request_id';

    protected $fillable = [
        'requester_name',
        'requester_organization',
        'requester_address',
        'requester_email',
        'requester_contact',
        'incident_date',
        'incident_time',
        'names_involved',
        'incident_description',
        'footage_date',
        'footage_time_start',
        'footage_time_end',
        'request_nature',
        'status',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'footage_date'  => 'date',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    // -------------------------
    // Relationships
    // -------------------------

    public function messages()
    {
        return $this->hasMany(RequestMessage::class, 'request_id', 'request_id');
    }
}