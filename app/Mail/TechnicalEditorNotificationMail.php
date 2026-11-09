<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TechnicalEditorNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Admin $technicalEditor;
    public Application $application;
    public ?string $comment;
    public string $url;

    /**
     * Create a new message instance.
     */
    public function __construct(Admin $technicalEditor, Application $application, ?string $comment, string $url)
    {
        $this->technicalEditor = $technicalEditor;
        $this->application = $application;
        $this->comment = $comment;
        $this->url = $url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Accepted Manuscript for Technical Processing - ' . ($this->application->application_id ?? $this->application->uuid))
            ->markdown('emails.technicalEditor.accepted')
            ->with([
                'technicalEditor' => $this->technicalEditor,
                'application' => $this->application,
                'comment' => $this->comment,
                'url' => $this->url,
            ]);
    }
}