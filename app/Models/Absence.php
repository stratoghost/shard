<?php

namespace App\Models;

use App\AbsenceType;
use App\Models\Contracts\AttachableContract;
use App\Models\Contracts\TraceableContract;
use App\Observers\AbsenceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[ObservedBy(AbsenceObserver::class)]
class Absence extends Model implements AttachableContract, TraceableContract
{
    use HasFactory;

    protected $fillable = [
        'date',
        'minutes_absent',
        'terminal_id',
        'type',
        'authorised_at',
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

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'authorised_at' => 'datetime',
            'type' => AbsenceType::class,
        ];
    }
}
