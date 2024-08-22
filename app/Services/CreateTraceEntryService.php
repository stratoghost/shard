<?php

namespace App\Services;

use App\Exceptions\Traces\TraceContentCannotBeEmptyException;
use App\Models\Session;
use App\Models\Trace;
use Illuminate\Database\Eloquent\Model;
use Throwable;

readonly class CreateTraceEntryService
{
    public function __construct(protected Session $session) {}

    /**
     * @throws Throwable
     */
    public function createTrace(array $attributes): Trace
    {
        throw_if(empty($attributes['content']), TraceContentCannotBeEmptyException::class);

        return $this->session->traces()->create([
            'session_id' => $this->session->id,
            'terminal_id' => $this->session->terminal_id,
            'content' => $attributes['content'],
            'type' => $attributes['type'] ?? null,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function attachTrace(array $attributes, Model $traceable): Trace
    {
        throw_if(empty($attributes['content']), TraceContentCannotBeEmptyException::class);

        return Trace::create([
            'session_id' => $this->session->id,
            'terminal_id' => $this->session->terminal_id,
            'content' => $attributes['content'],
            'type' => $attributes['type'] ?? null,
            'traceable_type' => $traceable->getMorphClass(),
            'traceable_id' => $traceable->getKey(),
        ]);
    }
}
