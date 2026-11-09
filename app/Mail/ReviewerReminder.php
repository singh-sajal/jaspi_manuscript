<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReviewerReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Application $application;
    public Admin $reviewer;
    public string $type; // 'internal' or 'external'
    public Admin|null $editor;
    public $url;
    public ?int $reminderNumber;
    public $deadline;

    /**
     * Create a new message instance.
     */
    public function __construct(
        Application $application,
        Admin $reviewer,
        string $type = 'external',
        ?Admin $editor = null,
        $url = null,
        ?int $reminderNumber = null,
        $deadline = null
    ) {
        $this->application    = $application;
        $this->reviewer       = $reviewer;
        $this->type           = $type;
        $this->editor         = $editor;
        $this->url            = $url;
        $this->reminderNumber = $reminderNumber;
        $this->deadline       = $deadline;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = "Reminder: Pending Manuscript Review - " . ($this->application->title ?? '');

        if ($this->reminderNumber) {
            $subject = "Reminder #{$this->reminderNumber}: Pending Manuscript Review - " . ($this->application->title ?? '');
        }

        return $this->subject($subject)
            ->view('emails.reviewer.remindReviewers') // Points to the new blade template
            ->with([
                'application'    => $this->application,
                'reviewer'       => $this->reviewer,
                'type'           => $this->type,
                'editor'         => $this->editor,
                'reminderNumber' => $this->reminderNumber,
                'deadline'       => $this->deadline,
            ]);
    }
}
