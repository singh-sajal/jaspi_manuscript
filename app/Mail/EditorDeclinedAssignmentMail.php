<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EditorDeclinedAssignmentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Admin $assigner;
    public Application $application;
    public Admin $editor;
    public ?string $reason;
    public string $url;

    /**
     * Create a new message instance.
     */
    public function __construct(Admin $assigner, Application $application, Admin $editor, ?string $reason, string $url)
    {
        $this->assigner = $assigner;
        $this->application = $application;
        $this->editor = $editor;
        $this->reason = $reason;
        $this->url = $url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Handling Editor Declined Assignment - ' . ($this->application->application_id ?? $this->application->uuid))
            ->markdown('emails.editor.declined')
            ->with([
                'assigner' => $this->assigner,
                'application' => $this->application,
                'editor' => $this->editor,
                'reason' => $this->reason,
                'url' => $this->url,
            ]);
    }
}