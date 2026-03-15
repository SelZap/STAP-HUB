<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admin extends Authenticatable
{
    protected $primaryKey = 'admin_id';

    public $timestamps = false;

    protected $fillable = [
        'admin_name',
        'email',
        'password_hash',
        'is_superuser',
        'last_login',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'is_superuser' => 'boolean',
            'created_at'   => 'datetime',
            'last_login'   => 'datetime',
        ];
    }

    // Relationships
    public function activityLogs(): HasMany
    {
        return $this->hasMany(AdminActivityLog::class, 'admin_id', 'admin_id');
    }

    public function handledRequests(): HasMany
    {
        return $this->hasMany(FootageRequest::class, 'handled_by', 'admin_id');
    }

    public function requestMessages(): HasMany
    {
        return $this->hasMany(RequestMessage::class, 'admin_id', 'admin_id');
    }
}