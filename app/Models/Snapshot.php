<?php

namespace App\Models;

use App\Models\Contracts\TraceableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Snapshot extends Model implements TraceableContract
{
    use HasFactory;

    protected $fillable = [
        'time_clock_id',
        'terminal_id',
        'session_id',
        'minutes_given',
        'minutes_expected',
        'balance',
    ];

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function timeClock(): BelongsTo
    {
        return $this->belongsTo(TimeClock::class);
    }

    public function traces(): MorphMany
    {
        return $this->morphMany(Trace::class, 'traceable');
    }

    public function links(): MorphToMany
    {
        return $this->morphToMany(Trace::class, 'linkable', 'trace_links')->withPivot('trace_link_type');
    }
}
