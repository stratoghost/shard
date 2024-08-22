<?php

namespace App\Services;

use App\Exceptions\Common\DuplicateModelException;
use App\Exceptions\Terminals\EmptyTerminalIdentifierException;
use App\Models\Terminal;
use App\Models\User;

readonly class TerminalCreationService
{
    public function __construct(protected User $user) {}

    public function createTerminal(string $identifier): Terminal
    {
        if (empty($identifier)) {
            throw new EmptyTerminalIdentifierException;
        }

        if (Terminal::where('identifier', $identifier)->exists()) {
            throw new DuplicateModelException;
        }

        return Terminal::create([
            'identifier' => $identifier,
            'user_id' => $this->user->id,
        ]);
    }
}
