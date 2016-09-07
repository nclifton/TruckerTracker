<?php

namespace TruckerTracker\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use TruckerTracker\User;

class ResetPassword extends Notification
{
    //TODO use Queueable;
    protected $token;

    /**
     * Create a new notification instance.
     *
     * @param $token
     * @param User $user
     */
    public function __construct($token)
    {
        $this->token = $token;
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
        $appName = config('app.name', '');
        return (new MailMessage)
            ->subject("Your $appName Password Reset Link")
            ->greeting('Hi '.$notifiable->name)
            ->line("You recently requested to reset your password for your $appName account. Click the button below to reset it.")
            ->action('Reset Your Password', url('password/reset', $this->token))
            ->line('If you did not request a password reset, please ignore this email or reply to let us know. This password reset is only valid for the next 30 minutes.')
            ->line('Thanks,')
            ->line("Neil and the $appName Team")
            ->line('P.S. We also love hearing from you and helping you with any issues you have. Please reply to this email if you want to ask a question or just say hi.');

    }


}
