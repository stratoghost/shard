<?php

namespace App\Models;

use App\Models\Contracts\TraceableContract;
use App\Observers\SessionObserver;
use App\SessionType;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[ObservedBy(SessionObserver::class)]
class Session extends Model implements TraceableContract
{
    use HasFactory;

    protected $fillable = [
        'started_at',
        'ended_at',
        'expected_minutes',
        'terminal_id',
        'type',
    ];

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    public function timeClocks(): HasMany
    {
        return $this->hasMany(TimeClock::class);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(Snapshot::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function traces(): MorphMany
    {
        return $this->morphMany(Trace::class, 'traceable');
    }

    public function links(): MorphToMany
    {
        return $this->morphToMany(Trace::class, 'linkable', 'trace_links')->withPivot('trace_link_type');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'type' => SessionType::class,
        ];
    }
}
