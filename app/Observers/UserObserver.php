<?php

namespace App\Observers;

use App\Models\User;
use App\Services\TerminalCreationService;
use Faker\Factory;

class UserObserver
{
    public function created(User $user): void
    {
        $terminalCreationService = new TerminalCreationService($user);

        $identifier = Factory::create()->regexify('^[A-Z]{3}_[0-9]{3}$');

        $terminalCreationService->createTerminal($identifier);
    }
}
