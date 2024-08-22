<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('home', absolute: false), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="register" class="space-y-6">
        <flux:field>
            <flux:label>Name</flux:label>
            <flux:input wire:model.defer="name" name="name" type="text" required autofocus autocomplete="name"/>
            <flux:error name="name"/>
        </flux:field>
        <flux:field>
            <flux:label>Email address</flux:label>
            <flux:input wire:model.defer="email" name="email" type="email" required autocomplete="username"/>
            <flux:error name="email"/>
        </flux:field>
        <flux:field>
            <flux:label>Password</flux:label>
            <flux:input wire:model.defer="password" name="password" type="password" required viewable autocomplete="new-password"/>
            <flux:error name="password"/>
        </flux:field>
        <flux:field>
            <flux:label>Confirm Password</flux:label>
            <flux:input wire:model.defer="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"/>
            <flux:error name="password_confirmation"/>
        </flux:field>

        <div class="flex items-center justify-end mt-4">
            <flux:button variant="ghost" href="{{ route('login') }}" wire:navigate>Already registered?</flux:button>
            <flux:button variant="primary" type="submit">Register</flux:button>
        </div>
    </form>
</div>
