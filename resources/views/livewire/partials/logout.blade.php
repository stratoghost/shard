<?php

use App\Livewire\Actions\Logout;

return new class extends Livewire\Volt\Component {
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<flux:navlist.item icon="arrow-right-start-on-rectangle" wire:click="logout" wire:navigate>
    Log out
</flux:navlist.item>

