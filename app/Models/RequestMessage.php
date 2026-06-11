<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestMessage extends Model
{
    protected $table = 'request_messages';
    protected $primaryKey = 'message_id';

    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'sender_type',
        'admin_id',
        'message',
        'requirement_list',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    // Relationships
    public function footageRequest(): BelongsTo
    {
        return $this->belongsTo(FootageRequest::class, 'request_id', 'request_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'admin_id');
    }
}