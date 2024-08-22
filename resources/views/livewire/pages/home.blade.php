<?php

use App\Models\Contracts\TraceableContract;
use App\Models\Terminal;
use App\Models\Session;
use App\Services\CreateTraceEntryService;
use App\TraceType;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {

    use \Livewire\WithFileUploads;

    #[Locked]
    public string $fileUploadKey;

    public array $files;

    #[Validate('required|string|min:3|max:255')]
    public ?string $content = null;

    #[Validate(['required', new Enum(TraceType::class)])]
    public TraceType $type = TraceType::Normal;

    #[Locked]
    public ?TraceableContract $traceable = null;

    #[Computed]
    public function terminal(): Terminal
    {
        return auth()->user()->terminals()->first();
    }

    #[Computed]
    public function session(): ?Session
    {
        return $this->terminal->sessions()->active()->first();
    }

    public function mount(): void
    {
        $this->traceable = $this->traceable ?? $this->session;

        if ($this->traceable === null) {
            $this->isReady = false;
        }

        $this->fileUploadKey = uniqid();
    }

    public function createTrace(): void
    {
        $this->validate();

        $trace = resolve(CreateTraceEntryService::class, [
            'session' => $this->session,
        ])->attachTrace([
            'content' => $this->content,
            'type' => $this->type,
        ], $this->traceable);

        $attachmentUploadService = resolve(\App\Services\AttachmentUploadService::class, [
            'terminal' => $this->terminal,
        ]);

        foreach ($this->files as $file) {
            $attachmentUploadService->createAttachment($trace, $file);
        }

        Flux::toast('Your log entry has been created successfully');

        $this->reset('content');

        $this->fileUploadKey = uniqid();
    }
}
?>

<div class="flex flex-col min-h-full space-y-8">
    <div class="flex-1">
        <div class="flex justify-between items-center space-x-4 mb-6">
            <flux:heading size="xl">{{ now()->toFormattedDayDateString() }}</flux:heading>
        </div>
        <flux:separator variant="subtle"/>
    </div>
    <flux:separator variant="subtle"/>
    <flux:card>
        @if ( ! is_null($this->session))
            @if($errors->any())
                <div class="rounded-md bg-red-50 dark:bg-red-900 p-4 mb-8">
                    <div class="flex">
                        <div class="shrink-0">
                            <svg class="size-5 text-red-400 dark:text-red-300" viewBox="0 0 20 20"
                                 fill="currentColor"
                                 aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd"
                                      d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-100">
                                There
                                were {{ $errors->count() }} {{ \Illuminate\Support\Str::plural('error', $errors->count()) }}
                                with your submission
                            </h3>
                            <div class="mt-2 text-sm text-red-700 dark:text-red-200">
                                <ul role="list" class="list-disc space-y-1 pl-5">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <form wire:submit="createTrace" class="space-y-8">
                <div class="flex justify-end items-center space-x-4">
                    <flux:radio.group wire:model="type" size="sm" variant="segmented">
                        @foreach(TraceType::loggingTypes() as $traceType)
                            <flux:radio value="{{ $traceType->value }}">{{ $traceType->name }}</flux:radio>
                        @endforeach
                    </flux:radio.group>
                    <flux:spacer/>
                    <flux:radio.group wire:model="type" size="sm" variant="segmented">
                        @foreach(TraceType::interactionTypes() as $traceType)
                            <flux:radio value="{{ $traceType->value }}">{{ $traceType->name }}</flux:radio>
                        @endforeach
                    </flux:radio.group>
                    <flux:button size="sm" icon="document-check" variant="filled" type="submit">Create entry
                    </flux:button>
                </div>
                <flux:editor wire:model="content" placeholder="Write a log entry" required />
                <flux:separator horizontal/>
                <flux:tab.group>
                    <flux:tabs variant="segmented" size="sm">
                        <flux:tab name="persons" icon="user-circle">Persons</flux:tab>
                        <flux:tab name="collections" icon="hashtag">Collections</flux:tab>
                        <flux:tab name="attachments" icon="arrow-up-tray">Attachments</flux:tab>
                        <flux:tab name="tasks" icon="rectangle-stack">Tasks</flux:tab>
                    </flux:tabs>
                    <flux:tab.panel name="persons">
                        <flux:card></flux:card>
                    </flux:tab.panel>
                    <flux:tab.panel name="collections">
                        <flux:card></flux:card>
                    </flux:tab.panel>
                    <flux:tab.panel name="attachments">
                        <flux:card wire:key="{{ $this->fileUploadKey }}">
                            <flux:input type="file" wire:model="files" multiple/>
                        </flux:card>
                    </flux:tab.panel>
                    <flux:tab.panel name="tasks">
                        <flux:card></flux:card>
                    </flux:tab.panel>
                </flux:tab.group>
            </form>
        @else
            <div class="flex flex-col items-center justify-center space-y-4">
                <flux:heading size="lg">No active session</flux:heading>
            </div>
        @endif
    </flux:card>
</div>
