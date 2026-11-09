<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to every reviewer who actually submitted a review once the manuscript
 * they reviewed has been published (with its DOI).
 *
 * This is the second reviewer-facing milestone: the final decision email goes
 * out when the assistant editor decides, this one when the article actually
 * goes live.
 */
class ReviewerPublishedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Admin $reviewer;
    public Application $application;
    public ?string $doi;
    public string $url;

    /**
     * Create a new message instance.
     */
    public function __construct(Admin $reviewer, Application $application, ?string $doi, string $url)
    {
        $this->reviewer = $reviewer;
        $this->application = $application;
        $this->doi = $doi;
        $this->url = $url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $reference = $this->application->application_id ?? $this->application->uuid;

        return $this->subject('Manuscript Published - ' . $reference)
            ->markdown('emails.reviewer.published')
            ->with([
                'reviewer' => $this->reviewer,
                'application' => $this->application,
                'doi' => $this->doi,
                'doiUrl' => doiLinkUrl($this->doi),
                'url' => $this->url,
            ]);
    }
}
