<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<flux:card>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>
    <form wire:submit="updatePassword" class="mt-6 space-y-6">
        <flux:field>
            <flux:label for="update_password_current_password">Current Password</flux:label>
            <flux:input wire:model="current_password" id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"></flux:input>
            <flux:error name="current_password"></flux:error>
        </flux:field>

        <flux:field>
            <flux:label for="update_password_password">New Password</flux:label>
            <flux:input wire:model="password" id="update_password_password" name="password" type="password" autocomplete="new-password"></flux:input>
            <flux:error name="password"></flux:error>
        </flux:field>

        <flux:field>
            <flux:label for="update_password_password_confirmation">Confirm Password</flux:label>
            <flux:input wire:model="password_confirmation" id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"></flux:input>
            <flux:error name="password_confirmation"></flux:error>
        </flux:field>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="password-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</flux:card>
