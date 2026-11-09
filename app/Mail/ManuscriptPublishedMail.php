<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ManuscriptPublishedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Application $application;
    public ?string $doi;
    public string $url;

    /**
     * Create a new message instance.
     */
    public function __construct(Application $application, ?string $doi, string $url)
    {
        $this->application = $application;
        $this->doi = $doi;
        $this->url = $url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your Manuscript is Published - ' . ($this->application->application_id ?? $this->application->uuid))
            ->view('emails.author.published')
            ->with([
                'application' => $this->application,
                'doi' => $this->doi,
                'url' => $this->url,
            ]);
    }
}