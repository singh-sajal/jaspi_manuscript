<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\Application;
use App\Models\ReviewAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Sent to the handling editor when a reviewer passes their timeout deadline.
 * The reviewer is NOT unassigned — this is a notification only.
 */
class ReviewerTimeoutNotice extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Application $application;
    public ReviewAssignment $assignment;
    public ?Admin $editor;
    public $url;

    public function __construct(
        Application $application,
        ReviewAssignment $assignment,
        ?Admin $editor = null,
        $url = null
    ) {
        $this->application = $application;
        $this->assignment  = $assignment;
        $this->editor      = $editor;
        $this->url         = $url;
    }

    public function build()
    {
        $reviewer = $this->assignment->reviewer;

        $subject = 'Reviewer overdue: '
            . ($this->application->application_id ?? ($this->application->title ?? 'manuscript'));

        return $this->subject($subject)
            ->view('emails.editor.reviewerTimeout')
            ->with([
                'application' => $this->application,
                'assignment'  => $this->assignment,
                'reviewer'    => $reviewer,
                'editor'      => $this->editor,
                'url'         => $this->url,
            ]);
    }
}
