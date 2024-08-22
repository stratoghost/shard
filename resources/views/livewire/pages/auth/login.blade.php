<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('home', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')"/>

    <form wire:submit="login" class="space-y-6">
        <flux:field>
            <flux:label>Email address</flux:label>
            <flux:input wire:model.defer="form.email" name="email" type="email" required autofocus
                        autocomplete="username"/>
            <flux:error name="form.email"/>
        </flux:field>
        <flux:field>
            <flux:label>Password</flux:label>
            <flux:input wire:model.defer="form.password" name="password" type="password" required viewable
                        autocomplete="current-password"/>
            <flux:error name="form.password"/>
        </flux:field>

        <!-- Remember Me -->
        <flux:checkbox wire:model.defer="form.remember" label="Remember me" />

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <flux:button variant="ghost" href="{{ route('password.request') }}" wire:navigate>Forgot your password?</flux:button>
            @endif
            <flux:button variant="primary" type="submit">Log in</flux:button>
        </div>
    </form>
</div>
