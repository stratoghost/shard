<?php

use App\Models\Terminal;
use App\Models\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    use \Livewire\WithPagination;

    public bool $includeArchived = false;
    public string $sortBy = 'name';
    public string $sortDirection = 'desc';
    public string $search = '';

    public ?\App\Models\Collection $collection = null;

    public array $createCollection = [
        'name' => '',
        'description' => '',
    ];

    public array $editCollection = [
        'name' => '',
        'description' => '',
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

    public function addCollection(): void
    {
        $validated = $this->validate([
            'createCollection.name' => ['required', 'string', 'max:255'],
            'createCollection.description' => ['nullable', 'string'],
        ]);

        $this->terminal->collections()->create($validated['createCollection']);

        Flux::toast(text: 'Collection created', heading: 'Success', variant: 'success');

        $this->modal('create-collection')->close();

        $this->resetValidation();

        $this->reset('createCollection');
    }

    #[Computed]
    public function collections(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return \App\Models\Collection::query()
            ->where('terminal_id', $this->terminal->id)
            ->when($this->includeArchived, fn ($query) => $query->withTrashed())
            ->when($this->search, fn ($query, $search) =>
            $query->where('name', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%"))
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

    public function showDeleteCollectionModal($collectionId): void
    {
        $collection = \App\Models\Collection::withTrashed($this->includeArchived)->findOrFail($collectionId);

        $this->collection = $collection;

        $this->modal('delete-collection')->show();
    }

    public function deleteCollection(): void
    {
        $this->collection->delete();

        Flux::toast(text: 'Collection deleted', heading: 'Success', variant: 'success');

        $this->modal('delete-collection')->close();

        $this->collection = null;
    }

    public function showEditCollectionModal($collectionId): void
    {
        $collection = \App\Models\Collection::withTrashed($this->includeArchived)->findOrFail($collectionId);

        $this->collection = $collection;

        $this->editCollection = $collection->only('name', 'description');

        $this->resetValidation();

        $this->modal('edit-collection')->show();
    }

    public function updateCollection(): void
    {
        $validated = $this->validate([
            'editCollection.name' => ['required', 'string', 'max:255'],
            'editCollection.description' => ['nullable', 'string'],
        ]);

        $this->collection->update($validated['editCollection']);

        Flux::toast(text: 'Collection updated', heading: 'Success', variant: 'success');

        $this->modal('edit-collection')->close();

        $this->resetValidation();

        $this->reset('editCollection');

        $this->collection = null;
    }

    public function showRestoreCollectionModal($collectionId): void
    {
        $collection = \App\Models\Collection::withTrashed($this->includeArchived)->findOrFail($collectionId);

        $this->collection = $collection;

        $this->modal('restore-collection')->show();
    }

    public function restoreCollection(): void
    {
        $this->collection->restore();

        Flux::toast(text: 'Collection restored', heading: 'Success', variant: 'success');

        $this->modal('restore-collection')->close();

        $this->collection = null;
    }
}

?>

<div class="flex flex-col min-h-full">
    <div class="flex justify-between items-center mb-6">
        <flux:heading size="xl">Collections</flux:heading>
        <div class="flex gap-4 items-center">
            <div class="w-full justify-end items-center flex mr-4">
                <flux:switch wire:model.live="includeArchived" class="mr-4"/>
                <flux:label>Include archived</flux:label>
            </div>
            <flux:input variant="filled" placeholder="Search..." icon="magnifying-glass" wire:model.live="search"/>
            <flux:modal.trigger name="create-collection">
                <flux:button variant="filled" icon="plus" :disabled="is_null($this->session)">New collection</flux:button>
            </flux:modal.trigger>
        </div>
    </div>
    <flux:separator variant="subtle"/>
    <flux:table :paginate="$this->collections">
        <flux:columns>
            <flux:column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">Collection title</flux:column>
            <flux:column sortable :sorted="$sortBy === 'description'" :direction="$sortDirection" wire:click="sort('description')">Description</flux:column>
        </flux:columns>

        <flux:rows>
            @foreach ($this->collections as $collection)
                <flux:row :key="$collection->id">
                    <flux:cell class="flex items-center gap-3">
                        {{ $collection->name }}
                    </flux:cell>

                    <flux:cell class="whitespace-nowrap">{{ $collection->description }}</flux:cell>

                    <flux:cell>
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" :disabled="is_null($this->session)"></flux:button>

                            <flux:menu>
                                <flux:menu.item as="button" icon="pencil" wire:click="showEditCollectionModal('{{ $collection->id }}')">Edit</flux:menu.item>
                                @if ($collection->trashed())
                                    <flux:menu.item icon="arrow-left-start-on-rectangle" wire:click="showRestoreCollectionModal('{{ $collection->id }}')">Restore</flux:menu.item>
                                @else
                                    <flux:menu.item icon="trash" wire:click="showDeleteCollectionModal('{{ $collection->id }}')">Delete</flux:menu.item>
                                @endif
                            </flux:menu>
                        </flux:dropdown>
                    </flux:cell>
                </flux:row>
            @endforeach
        </flux:rows>
    </flux:table>

    <!-- Create collection modal -->
    <flux:modal name="create-collection" class="md:w-9/12">
        <form wire:submit="addCollection" class="space-y-6">
            <div>
                <flux:heading size="lg">Create new collection</flux:heading>
                <flux:subheading>Fill in the details below to create a new collection.</flux:subheading>
            </div>
            <div class="space-y-4">
                <flux:input label="Name" placeholder="Training" wire:model="createCollection.name"/>
                <flux:input label="Description" placeholder="A collection of traces related to training" wire:model="createCollection.description"/>
            </div>
            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary">Create collection</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Confirm delete collection modal -->
    <flux:modal name="delete-collection" class="min-w-[22rem] space-y-6">
        <form wire:submit="deleteCollection" class="space-y-6">
            <div>
                <flux:heading size="lg">Archive collection?</flux:heading>

                <flux:subheading>
                    <p>{{ $this->collection?->name }} will be archived.</p>
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

    <!-- Edit person modal -->
    <flux:modal name="edit-collection" class="md:w-9/12">
        <form wire:submit="updateCollection" class="space-y-6">
            <div>
                <flux:heading size="lg">Modify collection</flux:heading>
                <flux:subheading>Update the form below to modify this collections' details</flux:subheading>
            </div>
            <div class="space-y-4">
                <flux:input label="Name" placeholder="Training" wire:model="editCollection.name"/>
                <flux:input label="Description" placeholder="A collection of traces related to training" wire:model="editCollection.description"/>
            </div>
            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary">Save changes</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Confirm restore collection modal -->
    <flux:modal name="restore-collection" class="min-w-[22rem] space-y-6">
        <form wire:submit="restoreCollection" class="space-y-6">
            <div>
                <flux:heading size="lg">Restore collection?</flux:heading>

                <flux:subheading>
                    <p>{{ $this->collection?->name }} will be restored from archive.</p>
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
