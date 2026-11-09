<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InactivityReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $application;
    public $authorName;
    public $body;
    public $url;
    public $waitingDays;

    /** Admin-authored subject. Kept off $subject, which Mailable already owns. */
    protected $subjectLine;

    /**
     * @param  \App\Models\Application  $application
     * @param  string  $subject  Admin-authored subject line.
     * @param  string|null  $body  Admin-authored message. When null/blank the
     *                              view falls back to a default explanation.
     * @param  int|null  $waitingDays  How long the author has been idle.
     */
    public function __construct(
        $application,
        $authorName,
        $subject,
        $body = null,
        $url = null,
        $waitingDays = null
    ) {
        $this->application = $application;
        $this->authorName  = $authorName;
        $this->subjectLine = $subject;
        $this->body        = $body;
        $this->url         = $url ?: route('author.application.show', $application->uuid);
        $this->waitingDays = $waitingDays;
    }

    public function build()
    {
        return $this->subject($this->subjectLine)
            ->view('emails.author.inactivityReminder');
    }
}
