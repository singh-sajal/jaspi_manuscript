<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChiefApprovalResponseMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Admin $requester;
    public Application $application;
    public string $decision;
    public ?string $response;
    public string $url;

    /**
     * Create a new message instance.
     */
    public function __construct(Admin $requester, Application $application, string $decision, ?string $response, string $url)
    {
        $this->requester = $requester;
        $this->application = $application;
        $this->decision = $decision;
        $this->response = $response;
        $this->url = $url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Chief\'s Decision - ' . ($this->application->application_id ?? $this->application->uuid))
            ->markdown('emails.chief.approvalResponse')
            ->with([
                'requester' => $this->requester,
                'application' => $this->application,
                'decision' => $this->decision,
                'response' => $this->response,
                'url' => $this->url,
            ]);
    }
}