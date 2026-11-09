<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PublicationAcceptedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Admin $publisher;
    public Application $application;
    public ?string $comment;
    public string $url;

    /**
     * Create a new message instance.
     */
    public function __construct(Admin $publisher, Application $application, ?string $comment, string $url)
    {
        $this->publisher = $publisher;
        $this->application = $application;
        $this->comment = $comment;
        $this->url = $url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Manuscript Accepted for Publication - ' . ($this->application->application_id ?? $this->application->uuid))
            ->markdown('emails.publisher.publicationAccepted')
            ->with([
                'publisher' => $this->publisher,
                'application' => $this->application,
                'comment' => $this->comment,
                'url' => $this->url,
            ]);
    }
}
