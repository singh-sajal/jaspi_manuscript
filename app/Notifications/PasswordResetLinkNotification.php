<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetLinkNotification extends Notification
{
    use Queueable;
    private $token, $url;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($redirectUrl = null, $token = null)
    {
        $this->token = $token;
        $this->url = $redirectUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Password Reset Link')
            ->view('emails.forgot-password', [
                'user' => $notifiable,
                'subject' => 'Your Password Reset Link',
                'token' => $this->token,
                'url' => $this->url
            ]);
    }


    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
