<?php

namespace App\Models;

use App\Models\Contracts\TraceableContract;
use App\Observers\AttachmentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(AttachmentObserver::class)]
class Attachment extends Model implements TraceableContract
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'terminal_id',
        'attachable_type',
        'attachable_id',
        'session_id',
        'label',
        'filename',
        'path',
    ];

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function attachable(): BelongsTo
    {
        return $this->morphTo('attachable');
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
