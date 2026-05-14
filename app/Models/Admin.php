<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Admin extends Authenticatable implements JWTSubject
{
    protected $primaryKey = 'admin_id';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'admin_name',
        'email',
        'password',
        'password_hash',
        'is_superuser',
        'last_login',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $appends = [
        'id',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'is_superuser' => 'boolean',
            'created_at'   => 'datetime',
            'last_login'   => 'datetime',
        ];
    }


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->admin_name,
            set: fn ($value) => ['admin_name' => $value],
        );
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->admin_id
        );
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => ['password_hash' => $value]
        );
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
