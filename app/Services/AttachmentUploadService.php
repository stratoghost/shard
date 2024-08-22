<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Contracts\AttachableContract;
use App\Models\Terminal;
use Illuminate\Http\UploadedFile;

readonly class AttachmentUploadService
{
    public function __construct(protected Terminal $terminal) {}

    public function createAttachment(AttachableContract $attachable, UploadedFile $uploadedFile): Attachment
    {
        $path = $uploadedFile->storeAs(
            'usercontent/'.hash('sha256', $this->terminal->id.'_root_'.$this->terminal->user_id).'/'.hash('sha256', $attachable->getTable().'_'.$this->terminal->id),
            $uploadedFile->hashName(),
        );

        $attachment = $attachable->attachments()->create([
            'terminal_id' => $this->terminal->id,
            'filename' => $uploadedFile->hashName(),
            'path' => $path,
        ]);

        return $attachment->refresh();
    }
}
