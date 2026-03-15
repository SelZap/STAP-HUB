<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FootageRequest extends Model
{
    protected $table = 'footage_requests';
    protected $primaryKey = 'request_id';

    public $timestamps = false;

    protected $fillable = [
        'camera_id',
        'requester_email',
        'requester_contact',
        'request_nature',
        'footage_date',
        'footage_time_start',
        'footage_time_end',
        'status',
        'handled_by',
    ];

    protected function casts(): array
    {
        return [
            'footage_date' => 'date',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }

    // Relationships
    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class, 'camera_id', 'camera_id');
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'handled_by', 'admin_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(RequestMessage::class, 'request_id', 'request_id');
    }
}