<?php

namespace App\Models;

use App\Models\Contracts\AttachableContract;
use App\Observers\TraceObserver;
use App\TraceType;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[ObservedBy(TraceObserver::class)]
class Trace extends Model implements AttachableContract
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'terminal_id',
        'traceable_type',
        'traceable_id',
        'type',
        'content',
    ];

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function absences(): MorphToMany
    {
        return $this->morphedByMany(Absence::class, 'linkable', 'trace_links');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function collections(): MorphToMany
    {
        return $this->morphedByMany(Collection::class, 'linkable', 'trace_links');
    }

    public function holidays(): MorphToMany
    {
        return $this->morphedByMany(Holiday::class, 'linkable', 'trace_links');
    }

    public function incidents(): MorphToMany
    {
        return $this->morphedByMany(Incident::class, 'linkable', 'trace_links');
    }

    public function people(): MorphToMany
    {
        return $this->morphedByMany(Person::class, 'linkable', 'trace_links');
    }

    public function sessions(): MorphToMany
    {
        return $this->morphedByMany(Session::class, 'linkable', 'trace_links');
    }

    public function snapshots(): MorphToMany
    {
        return $this->morphedByMany(Snapshot::class, 'linkable', 'trace_links');
    }

    public function tasks(): MorphToMany
    {
        return $this->morphedByMany(Task::class, 'linkable', 'trace_links');
    }

    public function timeClocks(): MorphToMany
    {
        return $this->morphedByMany(TimeClock::class, 'linkable', 'trace_links');
    }

    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'linkable', 'trace_links');
    }

    protected function casts(): array
    {
        return [
            'type' => TraceType::class,
        ];
    }
}
