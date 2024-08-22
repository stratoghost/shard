<?php

use App\Decorators\TimeDecorator;
use App\Models\Terminal;
use App\Services\SessionInstanceService;
use App\Services\SessionTimeRemainingService;
use App\SessionType;
use Livewire\Attributes\Computed;

return new class extends Livewire\Volt\Component {

    public ?SessionType $sessionType = null;

    public ?int $expectedMinutes = null;

    public ?int $remainingMinutes = null;

    public function mount(): void
    {
        if (is_null($this->session)) {
            $this->sessionType = SessionType::StandardDuration;
            $this->expectedMinutes = SessionType::StandardDuration->expectedDuration();
        }
    }

    public function startSession(): void
    {
        $terminal = auth()->user()->terminals()->first();

        if (is_null($this->expectedMinutes)) {
            $this->expectedMinutes = $this->sessionType->expectedDuration();
        }

        resolve(SessionInstanceService::class, ['terminal' => $terminal])->startSession($this->sessionType, $this->expectedMinutes);

        Flux::toast(text: 'Your session has begun', heading: 'Session started', variant: 'success');

        $this->redirectIntended(route('home', false), true);
    }

    public function endSession(): void
    {
        resolve(SessionInstanceService::class, ['terminal' => $this->terminal])->endSession();

        Flux::toast(text: 'Your session has ended', heading: 'Session ended', variant: 'success');

        $this->redirectIntended(route('home', false), true);
    }

    public function updateExpectedMinutes(): void
    {
        $this->expectedMinutes = $this->sessionType->expectedDuration();
    }

    #[Computed]
    public function remainingMinutesInSession(): ?int
    {
        if (is_null($this->session)) {
            return null;
        }

        return resolve(SessionTimeRemainingService::class, ['session' => $this->session])->getPreCalculatedRemainingMinutes($this->session);
    }

    #[Computed]
    public function terminal(): Terminal
    {
        return auth()->user()->terminals()->first();
    }

    #[Computed]
    public function session(): ?\App\Models\Session
    {
        return $this->terminal->sessions()->active()->first();
    }
}

?>

<div>
    @if(is_null($this->session))
        <flux:modal.trigger name="session-starter">
            <flux:button icon="clock" variant="filled" size="sm">No session</flux:button>
        </flux:modal.trigger>

        <flux:modal name="session-starter" class="md:w-96 space-y-6">
            <div>
                <flux:heading size="lg">Start session</flux:heading>
                <flux:subheading>This will activate your terminal</flux:subheading>
            </div>
            <div>
                <flux:select variant="listbox" placeholder="Select option..." wire:model="sessionType" wire:change="updateExpectedMinutes">
                    <flux:option :value="SessionType::StandardDuration">
                        <div class="flex items-center gap-2">
                            <flux:icon.calendar variant="mini" class="text-zinc-400"/>
                            Standard duration
                        </div>
                    </flux:option>

                    <flux:option :value="SessionType::CustomDuration">
                        <div class="flex items-center gap-2">
                            <flux:icon.pencil-square variant="mini" class="text-zinc-400"/>
                            Altered hours
                        </div>
                    </flux:option>

                    <flux:option :value="SessionType::OnCall">
                        <div class="flex items-center gap-2">
                            <flux:icon.clock variant="mini" class="text-zinc-400"/>
                            Overtime
                        </div>
                    </flux:option>
                </flux:select>
            </div>

            <div>
                <flux:input type="number" placeholder="Expected duration" wire:model="expectedMinutes"/>
            </div>

            <div class="flex">
                <flux:spacer/>

                <flux:button wire:click="startSession" variant="primary">Start session</flux:button>
            </div>
        </flux:modal>
    @else
        <flux:button wire:poll.10s icon="stop" size="sm" variant="{{ $this->remainingMinutesInSession <= 0 ? 'danger' : 'ghost' }}" wire:click="endSession">
            @if ($this->remainingMinutesInSession == 0)
                End session
            @elseif ($this->remainingMinutesInSession < 0)
                {{ abs($this->remainingMinutesInSession) }} {{ \Illuminate\Support\Str::plural('minute', $this->remainingMinutesInSession) }}
                exceeded
            @else
                {{ resolve(TimeDecorator::class)->remainingMinutesToFormattedTime($this->remainingMinutesInSession) }}
                <span>/</span>
                {{ resolve(TimeDecorator::class)->expectedEndTimeFromExpectedMinutes(now(), $this->remainingMinutesInSession) }}
            @endif
        </flux:button>
    @endif
</div>
