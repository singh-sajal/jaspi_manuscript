<?php
// app/Mail/AdminMessageMail.php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class AdminMessageMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()

    {
        // Config::set('mail.from.name', $this->data['sender_name']);

        return $this
            // ->from($this->data['sender_email'], $this->data['sender_name'])
            ->from(Config::get('mail.from.address'), Config::get('mail.from.name'))
            ->subject($this->data['subject'])
            ->view('emails.mail-template')
            ->with('data', $this->data);
    }
}
