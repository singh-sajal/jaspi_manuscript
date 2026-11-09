<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;


class ReviewerAssigned extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Application $application;
    public Admin $reviewer;
    public string $type; // 'internal' or 'external'
    public Admin|null $editor;
    public $url;
    /** Final deadline for this assignment cycle (null when it could not be resolved). */
    public $dueAt;
    /** 'long' (14-7-3) for a first assignment, 'short' (7-3-2) afterwards. */
    public ?string $profile;

    /**
     * Create a new message instance.
     */
    public function __construct(Application $application, Admin $reviewer, string $type = 'external', ?Admin $editor = null, $url = null, $dueAt = null, ?string $profile = null)
    {
        $this->application = $application;
        $this->reviewer = $reviewer;
        $this->type = $type;
        $this->editor = $editor;
        $this->url = $url;
        $this->dueAt = $dueAt;
        $this->profile = $profile;

    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = " New Manuscript Assigned- " . ($this->application->title ?? $this->application->uuid);
        return $this->subject($subject)
                    ->markdown('emails.reviewer.assigned')
                    ->with([
                        'application' => $this->application,
                        'reviewer' => $this->reviewer,
                        'type' => $this->type,
                        'editor' => $this->editor,
                        'dueAt' => $this->dueAt,
                        'profile' => $this->profile,
                    ]);
    }
}
