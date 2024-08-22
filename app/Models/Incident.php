<?php

namespace App\Models;

use App\IncidentGradeType;
use App\IncidentType;
use App\Models\Contracts\AttachableContract;
use App\Models\Contracts\TraceableContract;
use App\Observers\IncidentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[ObservedBy(IncidentObserver::class)]
class Incident extends Model implements AttachableContract, TraceableContract
{
    use HasFactory;

    protected $fillable = [
        'description',
        'started_at',
        'resolved_at',
        'ended_at',
        'grade',
        'type',
        'time_to_resolution',
    ];

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    public function traces(): MorphMany
    {
        return $this->morphMany(Trace::class, 'traceable');
    }

    public function links(): MorphToMany
    {
        return $this->morphToMany(Trace::class, 'linkable', 'trace_links')->withPivot('trace_link_type');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'resolved_at' => 'datetime',
            'ended_at' => 'datetime',
            'grade' => IncidentGradeType::class,
            'type' => IncidentType::class,
            'time_to_resolution' => 'integer',
        ];
    }
}
