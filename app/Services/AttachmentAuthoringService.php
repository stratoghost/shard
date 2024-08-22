<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Terminal;
use Illuminate\Database\Eloquent\Model;

readonly class AttachmentAuthoringService
{
    public function __construct(protected Terminal $terminal) {}

    public function archiveAttachment(Attachment $attachment): void
    {
        $attachment->delete();
    }

    public function createAttachment(Model $model, array $attributes): Attachment
    {
        return $this->terminal->attachments()->create(array_merge($attributes, [
            'attachable_type' => get_class($model),
            'attachable_id' => $model->id,
        ]));
    }
}
