<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AuthorFinalDecisionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Application $application;
    public string $decision;
    public ?string $comment;
    public string $url;

    /**
     * Create a new message instance.
     */
    public function __construct(Application $application, string $decision, ?string $comment, string $url)
    {
        $this->application = $application;
        $this->decision = $decision;
        $this->comment = $comment;
        $this->url = $url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Final Decision on Your Manuscript - ' . ($this->application->application_id ?? $this->application->uuid))
            ->view('emails.author.finalDecision')
            ->with([
                'application' => $this->application,
                'decision' => $this->decision,
                'comment' => $this->comment,
                'url' => $this->url,
            ]);
    }
}