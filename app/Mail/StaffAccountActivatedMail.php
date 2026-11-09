<?php

namespace App\Mail;

use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffAccountActivatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Admin $staff;
    public $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Admin $staff)
    {
        $this->staff = $staff;
        $this->loginUrl = route('home');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Account Has Been Activated - JASPI'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.staff.account-activated',
            with: [
                'staff' => $this->staff,
                'loginUrl' => $this->loginUrl,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
