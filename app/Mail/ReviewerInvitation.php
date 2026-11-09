<?php

namespace App\Mail;

use App\Models\InviteReviewer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReviewerInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public InviteReviewer $inviteReviewer;
    public $url;

    /**
     * Create a new message instance.
     */
    public function __construct(InviteReviewer $inviteReviewer, $url)
    {
        $this->inviteReviewer = $inviteReviewer;
        $this->url = $url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Invitation to Join as a Peer Reviewer')
                    ->view('emails.reviewer.invitation')
                    ->with([
                        'email' => $this->inviteReviewer->email,
                        'url'   => $this->url,
                    ]);
    }
}
