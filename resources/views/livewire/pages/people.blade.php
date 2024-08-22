<?php

use App\Models\Terminal;
use App\Models\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    use \Livewire\WithPagination;

    public ?\App\Models\Person $person = null;

    public bool $includeArchived = false;
    public string $sortBy = 'first_name';
    public string $sortDirection = 'desc';
    public string $search = '';

    public array $createPerson = [
        'first_name' => '',
        'last_name' => '',
        'contact_number' => '',
        'email' => '',
        'type' => '',
    ];

    public array $editPerson = [
        'first_name' => '',
        'last_name' => '',
        'contact_number' => '',
        'email' => '',
        'type' => '',
    ];

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

    #[Computed]
    public function people(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return \App\Models\Person::query()
            ->where('terminal_id', $this->terminal->id)
            ->when($this->includeArchived, fn ($query) => $query->withTrashed())
            ->when($this->search, fn ($query, $search) =>
                $query->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('contact_number', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%"))
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(20);
    }

    public function sort($column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function addPerson(): void
    {
        $validated = $this->validate([
            'createPerson.first_name' => ['required', 'string'],
            'createPerson.last_name' => ['required', 'string'],
            'createPerson.contact_number' => ['sometimes', 'string'],
            'createPerson.email' => ['sometimes', 'email'],
            'createPerson.type' => ['required', 'string'],
        ]);

        $this->terminal->people()->create($validated['createPerson']);

        Flux::modal('create-person')->close();

        Flux::toast(text: 'Person record created', heading: 'Success', variant: 'success');

        $this->resetValidation();

        $this->reset('createPerson');
    }

    public function showDeletePersonModal($id): void
    {
        $person = \App\Models\Person::withTrashed($this->includeArchived)->findOrFail($id);

        $this->person = $person;

        $this->modal('delete-person')->show();
    }

    public function deletePerson(): void
    {
        $this->person->delete();

        Flux::toast(text: 'Person record deleted', heading: 'Success', variant: 'success');

        $this->modal('delete-person')->close();

        $this->person = null;
    }

    public function showPersonEditModal($id): void
    {
        $person = \App\Models\Person::withTrashed($this->includeArchived)->findOrFail($id);

        $this->person = $person;

        $this->editPerson = $person->only('first_name', 'last_name', 'contact_number', 'email', 'type.value');

        $this->resetValidation();

        $this->modal('edit-person')->show();
    }

    public function updatePerson(): void
    {
        $validated = $this->validate([
            'editPerson.first_name' => ['sometimes', 'string'],
            'editPerson.last_name' => ['sometimes', 'string'],
            'editPerson.contact_number' => ['sometimes', 'string'],
            'editPerson.email' => ['sometimes', 'email'],
            'editPerson.type' => ['sometimes', 'string'],
        ]);

        $this->person->update($validated['editPerson']);

        $this->modal('edit-person')->close();

        Flux::toast(text: 'Person record updated', heading: 'Success', variant: 'success');

        $this->reset('editPerson');

        $this->person = null;
    }

    public function showRestorePersonModal($id): void
    {
        $person = \App\Models\Person::withTrashed($this->includeArchived)->findOrFail($id);

        $this->person = $person;

        $this->modal('restore-person')->show();
    }

    public function restorePerson(): void
    {
        $this->person->restore();

        Flux::toast(text: 'Person record restored', heading: 'Success', variant: 'success');

        $this->modal('restore-person')->close();
    }
}

?>

<div class="flex flex-col min-h-full">
    <div class="flex justify-between items-center mb-6">
        <flux:heading size="xl">People directory</flux:heading>
        <div class="flex gap-4 items-center">
            <div class="w-full justify-end items-center flex mr-4">
                <flux:switch wire:model.live="includeArchived" class="mr-4" />
                <flux:label >Include archived</flux:label>
            </div>
            <flux:input variant="filled" placeholder="Search..." icon="magnifying-glass" wire:model.live="search"/>
            <flux:modal.trigger name="create-person">
                <flux:button variant="filled" icon="plus" :disabled="is_null($this->session)">Add person</flux:button>
            </flux:modal.trigger>
        </div>
    </div>
    <flux:separator variant="subtle"/>
    <flux:table :paginate="$this->people">
        <flux:columns>
            <flux:column sortable :sorted="$sortBy === 'first_name'" :direction="$sortDirection" wire:click="sort('first_name')">First name</flux:column>
            <flux:column sortable :sorted="$sortBy === 'last_name'" :direction="$sortDirection" wire:click="sort('last_name')">Last name</flux:column>
            <flux:column sortable :sorted="$sortBy === 'contact_number'" :direction="$sortDirection" wire:click="sort('contact_number')">Contact number</flux:column>
            <flux:column sortable :sorted="$sortBy === 'email'" :direction="$sortDirection" wire:click="sort('email')">Email address</flux:column>
            <flux:column sortable :sorted="$sortBy === 'type'" :direction="$sortDirection" wire:click="sort('type')">Record type</flux:column>
        </flux:columns>

        <flux:rows>
            @foreach ($this->people as $person)
                <flux:row :key="$person->id">
                    <flux:cell class="flex items-center gap-3">
                        <flux:avatar size="xs" src="{{ url('https://ui-avatars.com/api/?name=' . urlencode($person->name) . '&color=7F9CF5&background=EBF4FF') }}"/>

                        {{ $person->first_name }}
                    </flux:cell>

                    <flux:cell class="whitespace-nowrap">{{ $person->last_name }}</flux:cell>

                    <flux:cell class="whitespace-nowrap">{{ $person->contact_number }}</flux:cell>
                    <flux:cell class="whitespace-nowrap">{{ $person->email }}</flux:cell>

                    <flux:cell variant="strong">{{ $person->type->label() }}</flux:cell>

                    <flux:cell>
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" :disabled="is_null($this->session)"></flux:button>

                            <flux:menu>
                                <flux:menu.item as="button" icon="pencil" wire:click="showPersonEditModal('{{ $person->id }}')">Edit</flux:menu.item>
                                @if ($person->trashed())
                                    <flux:menu.item icon="arrow-left-start-on-rectangle" wire:click="showRestorePersonModal('{{ $person->id }}')">Restore</flux:menu.item>
                                @else
                                    <flux:menu.item icon="trash" wire:click="showDeletePersonModal('{{ $person->id }}')">Delete</flux:menu.item>
                                @endif
                            </flux:menu>
                        </flux:dropdown>
                    </flux:cell>
                </flux:row>
            @endforeach
        </flux:rows>
    </flux:table>

    <!-- Create person modal -->
    <flux:modal name="create-person" class="md:w-9/12">
        <form wire:submit="addPerson" class="space-y-6">
            <div>
                <flux:heading size="lg">Create person record</flux:heading>
                <flux:subheading>Fill in the form below to create a new person record</flux:subheading>
            </div>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="First name" placeholder="John" wire:model="createPerson.first_name"/>
                    <flux:input label="Last name" placeholder="Appleseed" wire:model="createPerson.last_name"/>
                </div>

                <flux:input label="Mobile number" type="text" placeholder="07777 777 777" mask="99999 999 999" wire:model="createPerson.contact_number"/>
                <flux:input label="Email address" type="email" placeholder="john.appleseed@apple.com" wire:model="createPerson.email"/>

                <flux:select label="Record type" placeholder="Choose person type..." wire:model="createPerson.type">
                    @foreach (\App\PersonType::cases() as $type)
                        <flux:option value="{{ $type->value }}">{{ $type->label() }}</flux:option>
                    @endforeach
                </flux:select>
            </div>
            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary">Create record</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Edit person modal -->
    <flux:modal name="edit-person" class="md:w-9/12">
        <form wire:submit="updatePerson" class="space-y-6">
            <div>
                <flux:heading size="lg">Change person record</flux:heading>
                <flux:subheading>Update the form below to change the person record</flux:subheading>
            </div>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="First name" placeholder="John" wire:model="editPerson.first_name"/>
                    <flux:input label="Last name" placeholder="Appleseed" wire:model="editPerson.last_name"/>
                </div>

                <flux:input label="Mobile number" type="text" placeholder="07777 777 777" mask="99999 999 999" wire:model="editPerson.contact_number"/>
                <flux:input label="Email address" type="email" placeholder="john.appleseed@apple.com" wire:model="editPerson.email"/>

                <flux:select label="Record type" placeholder="Choose person type..." wire:model="editPerson.type">
                    @foreach (\App\PersonType::cases() as $type)
                        <flux:option value="{{ $type->value }}">{{ $type->label() }}</flux:option>
                    @endforeach
                </flux:select>
            </div>
            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary">Save changes</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Confirm delete person modal -->
    <flux:modal name="delete-person" class="min-w-[22rem] space-y-6">
        <form wire:submit="deletePerson" class="space-y-6">
            <div>
                <flux:heading size="lg">Archive person record?</flux:heading>

                <flux:subheading>
                    <p>{{ $this->person?->name }} will be archived.</p>
                    <p>Are you sure you want to do this?</p>
                </flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="danger">I'm sure</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Confirm restore person modal -->
    <flux:modal name="restore-person" class="min-w-[22rem] space-y-6">
        <form wire:submit="restorePerson" class="space-y-6">
            <div>
                <flux:heading size="lg">Restore person record?</flux:heading>

                <flux:subheading>
                    <p>{{ $this->person?->name }} will be restored from archive.</p>
                    <p>Are you sure you want to do this?</p>
                </flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary">I'm sure</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
