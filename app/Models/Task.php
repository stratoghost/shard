<?php

namespace App\Models;

use App\Models\Contracts\AttachableContract;
use App\Models\Contracts\TraceableContract;
use App\Observers\TaskObserver;
use App\TaskPriorityType;
use App\TaskQueueType;
use App\TaskSourceType;
use App\TaskStateType;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(TaskObserver::class)]
class Task extends Model implements AttachableContract, TraceableContract
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'parent_id',

        'title',
        'description',

        'queue',
        'state',
        'priority',
        'source',

        'source_key',
        'source_url',

        'total_minutes_spent',

        'terminal_id',
    ];

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function traces(): MorphMany
    {
        return $this->morphMany(Trace::class, 'traceable');
    }

    public function links(): MorphToMany
    {
        return $this->morphToMany(Trace::class, 'linkable', 'trace_links')->withPivot('trace_link_type');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function worklogs(): HasMany
    {
        return $this->hasMany(Worklog::class);
    }

    public function scopeNotClosed($query)
    {
        return $query->whereIn('state', TaskStateType::nonFinalStates());
    }

    public function scopeClosed($query)
    {
        return $query->whereIn('state', TaskStateType::finalStates());
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'queue' => TaskQueueType::class,
            'state' => TaskStateType::class,
            'priority' => TaskPriorityType::class,
            'source' => TaskSourceType::class,
        ];
    }
}
