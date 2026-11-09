<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProofreadingMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $application;
    public $comment;
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($application, $comment, $url)
    {
        $this->application = $application;
        $this->comment = $comment;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Manuscript Proofreading Required: ' . $this->application->uuid)
                    ->view('emails.author.proofreading');
                    // Make sure this path matches where you create the blade file below
    }
}
