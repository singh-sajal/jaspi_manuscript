<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EditorAssignmentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $editor;
    public $application;
    public $assignedBy;
    public $url;

    public function __construct($editor, $application, $assignedBy = null, $url)
    {
        $this->editor = $editor;
        $this->application = $application;
        $this->assignedBy = $assignedBy;
        $this->url = $url;
    }

    public function build()
    {
        return $this->subject('New Manuscript Assigned to You')
            ->markdown('emails.editor.assignment')
            ->with([
                'application' => $this->application,
                'editor' => $this->editor,
                'assignedBy' => $this->assignedBy,
                'url' => $this->url,
            ]);
    }
}
