<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReviewerDeclinedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Admin $editor;
    public Application $application;
    public Admin $reviewer;
    public ?string $reason;
    public string $url;

    /**
     * Create a new message instance.
     */
    public function __construct(Admin $editor, Application $application, Admin $reviewer, ?string $reason, string $url)
    {
        $this->editor = $editor;
        $this->application = $application;
        $this->reviewer = $reviewer;
        $this->reason = $reason;
        $this->url = $url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Reviewer Declined Assignment - ' . ($this->application->application_id ?? $this->application->uuid))
            ->markdown('emails.reviewer.declined')
            ->with([
                'editor' => $this->editor,
                'application' => $this->application,
                'reviewer' => $this->reviewer,
                'reason' => $this->reason,
                'url' => $this->url,
            ]);
    }
}