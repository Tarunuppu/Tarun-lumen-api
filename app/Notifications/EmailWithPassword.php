<?php
namespace App\Notifications;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
class EmailWithPassword extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }
    public function toMail($notifiable)
    {
        //dd($notifiable->password);
        return (new MailMessage)
            ->subject(Lang::get('DO NOT SHARE WITH ANYONE'))
            ->line(Lang::get('Please find your temporary password below'))
            ->line(Lang::get('password:'.$notifiable->password))
            ->line(Lang::get('Please change the password as soon as possible'));
    }
 
}