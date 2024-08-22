<?php

namespace App\Observers;

use App\Models\Attachment;
use App\Models\Session;

class AttachmentObserver
{
    public function creating(Attachment $attachment): void
    {
        $terminalPrefix = explode('_', $attachment->terminal->identifier)[0];
        $countPreviousAttachments = Attachment::where('terminal_id', $attachment->terminal_id)->count() + 1;

        if (is_null($attachment->session_id)) {
            $attachment->session_id = $attachment->terminal->sessions()->whereNull('ended_at')->latest()->first()->id;
        }

        if (is_null($attachment->attachable_id)) {
            $attachment->attachable_id = $attachment->session_id;
            $attachment->attachable_type = Session::class;
        }

        $attachment->label = sprintf('%s/%s-%d/%s', $terminalPrefix, $attachment->session_id, $countPreviousAttachments, date('Ymd'));
    }
}
