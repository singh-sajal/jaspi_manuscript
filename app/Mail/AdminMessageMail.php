<?php
// app/Mail/AdminMessageMail.php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->from($this->data['sender_email'], $this->data['sender_name'])
            ->subject($this->data['subject'])
            ->view('emails.mail-template')
            ->with('data', $this->data);
    }
}
