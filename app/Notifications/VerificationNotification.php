<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationNotification extends Notification
{
    use Queueable;
    private $otp, $module;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($otp, $module = null)
    {
        $this->otp = $otp;
        $this->module  = $module;
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


        $email_body = view('emails.verification-code', ['otp' => $this->otp,])->render();;

        return (new MailMessage)
            ->subject('Account Verification')
            ->view('emails.mail', [
                'email_body' => $email_body,
                'subject' => 'Account Verification'
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
