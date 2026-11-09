<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuthorSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $manuscriptTitle;
    public $manuscriptId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($manuscriptTitle, $manuscriptId)
    {
        $this->manuscriptTitle = $manuscriptTitle;
        $this->manuscriptId = $manuscriptId;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Manuscript Submission Confirmation - JASPI',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.author_submission',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
