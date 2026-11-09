<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AuthorReviseMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Application $application;
    public ?string $comment;
    public string $url;

    /**
     * Create a new message instance.
     */
    public function __construct(Application $application, ?string $comment, string $url)
    {
        $this->application = $application;
        $this->comment = $comment;
        $this->url = $url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your Manuscript Requires Revision - JASPI')
            ->view('emails.author.revise')
            ->with([
                'application' => $this->application,
                'comment' => $this->comment,
                'url' => $this->url,
            ]);
    }
}