<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class AuthorResubmissionConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $manuscriptTitle;
    public $manuscriptId;

    /**
     * Create a new message instance.
     */
    public function __construct($manuscriptTitle, $manuscriptId)
    {
        $this->manuscriptTitle = $manuscriptTitle;
        $this->manuscriptId = $manuscriptId;
    }

    /**
     * Get the message envelope.
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Revised Manuscript Re-submission Confirmation - JASPI',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content()
    {
        return new Content(
            view: 'emails.author_resubmission',
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
