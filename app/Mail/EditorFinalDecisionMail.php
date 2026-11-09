<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EditorFinalDecisionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Admin $assigner;
    public Application $application;
    public string $decision;
    public ?string $comment;
    public string $url;

    /**
     * Create a new message instance.
     */
    public function __construct(Admin $assigner, Application $application, string $decision, ?string $comment, string $url)
    {
        $this->assigner = $assigner;
        $this->application = $application;
        $this->decision = $decision;
        $this->comment = $comment;
        $this->url = $url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Handling Editor Final Decision - ' . ($this->application->application_id ?? $this->application->uuid))
            ->markdown('emails.assigner.editorFinalDecision')
            ->with([
                'assigner' => $this->assigner,
                'application' => $this->application,
                'decision' => $this->decision,
                'comment' => $this->comment,
                'url' => $this->url,
            ]);
    }
}