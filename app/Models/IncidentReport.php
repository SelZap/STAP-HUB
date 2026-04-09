<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class IncidentReport extends Model
{
    protected $primaryKey = 'incident_id';

    protected $fillable = [
        'incident_date',
        'incident_time',
        'environmental_condition',
        'location_description',
        'vehicle_type',
        'vehicle_count',
        'people_hurt',
        'injured_count',
        'description',
        'reporting_party_name',
        'reporter_email',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'people_hurt'   => 'boolean',
            'reviewed_at'   => 'datetime',
            'created_at'    => 'datetime',
            'updated_at'    => 'datetime',
        ];
    }

    // -------------------------
    // Relationships
    // -------------------------

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by', 'admin_id');
    }

    // -------------------------
    // Scopes
    // -------------------------

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeReviewed(Builder $query): Builder
    {
        return $query->where('status', 'reviewed');
    }
}