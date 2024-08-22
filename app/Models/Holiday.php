<?php

namespace App\Models;

use App\Models\Contracts\TraceableContract;
use App\Observers\HolidayObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[ObservedBy(HolidayObserver::class)]
class Holiday extends Model implements TraceableContract
{
    use HasFactory;

    protected $fillable = [
        'date',
        'minutes_authorised',
        'terminal_id',
        'authorised_at',
        'cancelled_at',
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
            'cancelled_at' => 'datetime',
        ];
    }
}
