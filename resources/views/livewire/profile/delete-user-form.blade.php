<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;

return new class extends Livewire\Volt\Component {
    public string $password = '';

    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>


<flux:card class="space-y-6">
    <div>
        <flux:heading size="lg">Delete Account</flux:heading>
        <flux:subheading>
            <p>Once your account is deleted, all of its resources and data will be permanently deleted.</p>
            <p>Before deleting your account, please download any data or information that you wish to retain.</p>
        </flux:subheading>
    </div>
    <div class="space-y-6">
        <flux:modal.trigger name="confirm-user-deletion">
            <flux:button variant="danger">{{ __('Delete Account') }}</flux:button>
        </flux:modal.trigger>


        <flux:modal name="confirm-user-deletion">
            <form wire:submit="deleteUser" class="space-y-6">
                <div>
                    <flux:heading size="lg">Delete your account?</flux:heading>
                    <flux:subheading>
                        <p>You're about to permanently delete your account.</p>
                        <p>Please confirm by entering your account password.</p>
                    </flux:subheading>
                </div>

                <flux:field>
                    <flux:input type="password" placeholder="Your account password" wire:model.defer="password"/>
                    <flux:error name="password"/>
                </flux:field>

                <div class="flex gap-2">
                    <flux:spacer/>

                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button variant="danger" wire:click="deleteUser">Delete account</flux:button>
                </div>
            </form>
        </flux:modal>
    </div>
</flux:card>
