<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AssistantEditorAssignmentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Admin $assistantEditor;
    public Application $application;
    public ?string $remark;
    public string $url;

    /**
     * Create a new message instance.
     */
    public function __construct(Admin $assistantEditor, Application $application, ?string $remark, string $url)
    {
        $this->assistantEditor = $assistantEditor;
        $this->application = $application;
        $this->remark = $remark;
        $this->url = $url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('New Manuscript Assigned to You - JASPI')
            ->markdown('emails.assistantEditor.assignment')
            ->with([
                'assistantEditor' => $this->assistantEditor,
                'application' => $this->application,
                'remark' => $this->remark,
                'url' => $this->url,
            ]);
    }
}