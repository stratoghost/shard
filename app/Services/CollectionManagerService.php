<?php

namespace App\Services;

use App\Exceptions\Collections\CollectionAlreadyExistsException;
use App\Exceptions\Common\ModelNotTrashedException;
use App\Models\Collection as CollectionModel;
use App\Models\Terminal;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection as CollectionIterable;

readonly class CollectionManagerService
{
    public function __construct(protected Terminal $terminal) {}

    public function archiveCollection(CollectionModel $collectionModel): void
    {
        if ($collectionModel->trashed()) {
            throw (new ModelNotFoundException)->setModel(CollectionModel::class, $collectionModel->id);
        }

        $collectionModel->delete();
    }

    public function createCollection(array $attributes): CollectionModel
    {
        if ($this->terminal->collections()->where('name', $attributes['name'])->exists()) {
            throw new CollectionAlreadyExistsException;
        }

        return $this->terminal->collections()->create($attributes);
    }

    /** @deprecated Use the model directly */
    public function listArchivedCollections(): CollectionIterable
    {
        return $this->terminal->collections()->onlyTrashed()->get();
    }

    /** @deprecated Use the model directly */
    public function listCollections(): CollectionIterable
    {
        return $this->terminal->collections()->get();
    }

    public function modifyCollection(CollectionModel $collectionModel, array $attributes): CollectionModel
    {
        if ($this->terminal->collections()->where('name', $attributes['name'])->exists()) {
            throw new CollectionAlreadyExistsException;
        }

        $collectionModel->update($attributes);

        return $collectionModel;
    }

    public function restoreCollection(CollectionModel $collectionModel): void
    {
        if (! $collectionModel->trashed()) {
            throw new ModelNotTrashedException;
        }

        $collectionModel->restore();
    }
}
