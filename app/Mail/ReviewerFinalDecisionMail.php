<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to every reviewer who actually submitted a review once the editorial
 * team has made the final decision on the manuscript (accepted, rejected or
 * withdrawn). Purely informational: thanks the reviewer for their work.
 */
class ReviewerFinalDecisionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Admin $reviewer;
    public Application $application;
    public string $decision;
    public ?string $comment;
    public string $url;

    /**
     * Create a new message instance.
     */
    public function __construct(Admin $reviewer, Application $application, string $decision, ?string $comment, string $url)
    {
        $this->reviewer = $reviewer;
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
        $reference = $this->application->application_id ?? $this->application->uuid;

        return $this->subject('Final Decision - ' . $reference . ': ' . $this->decision)
            ->markdown('emails.reviewer.finalDecision')
            ->with([
                'reviewer' => $this->reviewer,
                'application' => $this->application,
                'decision' => $this->decision,
                'comment' => $this->comment,
                'url' => $this->url,
            ]);
    }
}
