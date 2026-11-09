<?php

namespace App\Mail;

use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffCredentialsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Admin $staff;
    public $password;

    /**
     * Create a new message instance.
     */
    public function __construct(Admin $staff, $password)
    {
        $this->staff = $staff;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Account Credentials - Welcome to JASPI'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.staff.credentials',
            with: [
                'staff' => $this->staff,
                'password' => $this->password,
                'loginUrl' => route('home'),
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
