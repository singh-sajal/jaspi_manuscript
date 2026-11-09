<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PublisherAssignmentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Admin $publisher;
    public Application $application;
    public string $url;

    /**
     * Create a new message instance.
     */
    public function __construct(Admin $publisher, Application $application, string $url)
    {
        $this->publisher = $publisher;
        $this->application = $application;
        $this->url = $url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('New Manuscript Assigned to You for Publication - JASPI')
            ->markdown('emails.publisher.assignment')
            ->with([
                'publisher' => $this->publisher,
                'application' => $this->application,
                'url' => $this->url,
            ]);
    }
}