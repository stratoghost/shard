<?php

use App\Models\Terminal;
use App\Services\TerminalStateManagerService;
use App\TerminalStateType;
use Livewire\Attributes\Computed;

return new class extends Livewire\Volt\Component {

    public ?TerminalStateType $selectedTerminalState = null;

    public function mount(): void
    {
        $this->selectedTerminalState = $this->terminal->state;
    }

    public function changeState(): void
    {
        if (! $this->terminal->sessions()->active()->first()) {
            $this->selectedTerminalState = $this->terminal->state;

            Flux::toast(text: 'You cannot change your terminal state before starting a session', heading: 'Missing active session', variant: 'danger');

            return;
        }

        $terminalStateManager = resolve(TerminalStateManagerService::class, [
            'terminal' => $this->terminal,
        ]);

        $terminalStateManager->activateState(
            $this->selectedTerminalState
        );
    }

    #[Computed]
    public function terminal(): Terminal
    {
        return auth()->user()->terminals()->first();
    }

    #[Computed]
    public function trackableState(): bool
    {
        return in_array($this->terminal->state, TerminalStateType::trackableStates());
    }
}

?>

<flux:dropdown align="end">
    <flux:button icon="key" variant="{{ $this->trackableState ? 'ghost' : 'subtle' }}" size="sm">
        <span>{{ $this->selectedTerminalState->name }}</span>
    </flux:button>

    <flux:menu>
        <flux:menu.radio.group wire:model="selectedTerminalState" wire:change="changeState">
            <flux:menu.radio
                    value="{{ TerminalStateType::Available }}">{{ TerminalStateType::Available->name }}</flux:menu.radio>
            <flux:menu.radio value="{{ TerminalStateType::Busy }}">{{ TerminalStateType::Busy->name }}</flux:menu.radio>
            <flux:menu.radio
                    value="{{ TerminalStateType::Meeting }}">{{ TerminalStateType::Meeting->name }}</flux:menu.radio>
            <flux:menu.radio
                    value="{{ TerminalStateType::Break }}">{{ TerminalStateType::Break->name }}</flux:menu.radio>
            <flux:menu.separator/>
            <flux:menu.radio
                    value="{{ TerminalStateType::Incident }}">{{ TerminalStateType::Incident->name }}</flux:menu.radio>
            <flux:menu.separator/>
            <flux:menu.radio
                    value="{{ TerminalStateType::Unavailable }}">{{ TerminalStateType::Unavailable->name }}</flux:menu.radio>
            <flux:menu.radio
                    value="{{ TerminalStateType::Holiday }}">{{ TerminalStateType::Holiday->name }}</flux:menu.radio>
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>
