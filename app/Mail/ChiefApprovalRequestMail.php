<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChiefApprovalRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Admin $chief;
    public Application $application;
    public ?Admin $requester;
    public string $url;

    /**
     * Create a new message instance.
     */
    public function __construct(Admin $chief, Application $application, ?Admin $requester, string $url)
    {
        $this->chief = $chief;
        $this->application = $application;
        $this->requester = $requester;
        $this->url = $url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Final Approval Request - ' . ($this->application->application_id ?? $this->application->uuid))
            ->markdown('emails.chief.approvalRequest')
            ->with([
                'chief' => $this->chief,
                'application' => $this->application,
                'requester' => $this->requester,
                'url' => $this->url,
            ]);
    }
}