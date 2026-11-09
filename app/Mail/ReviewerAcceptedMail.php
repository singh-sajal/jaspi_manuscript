<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReviewerAcceptedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Admin $editor;
    public Application $application;
    public Admin $reviewer;
    public bool $isInternal;
    public string $url;

    /**
     * Create a new message instance.
     */
    public function __construct(Admin $editor, Application $application, Admin $reviewer, bool $isInternal, string $url)
    {
        $this->editor = $editor;
        $this->application = $application;
        $this->reviewer = $reviewer;
        $this->isInternal = $isInternal;
        $this->url = $url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Reviewer Accepted Assignment - ' . ($this->application->application_id ?? $this->application->uuid))
            ->markdown('emails.reviewer.accepted')
            ->with([
                'editor' => $this->editor,
                'application' => $this->application,
                'reviewer' => $this->reviewer,
                'slot' => $this->isInternal ? 'Internal Reviewer' : 'External Reviewer',
                'url' => $this->url,
            ]);
    }
}
