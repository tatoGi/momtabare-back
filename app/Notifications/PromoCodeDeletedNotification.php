<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PromoCodeDeletedNotification extends Notification
{
    use Queueable;

    protected $code;
    protected $discountPercentage;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $code, float $discountPercentage)
    {
        $this->code = $code;
        $this->discountPercentage = $discountPercentage;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Promo Code No Longer Available')
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('We wanted to inform you that a promotional code has been removed.')
            ->line('**Promo Code:** ' . $this->code)
            ->line('**Discount:** ' . $this->discountPercentage . '% OFF')
            ->line('This promo code is no longer valid and cannot be used.')
            ->line('Don\'t worry! We have many other great deals available for you.')
            ->action('Browse Deals', 'https://www.momtabare.com')
            ->line('Thank you for your understanding!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'code' => $this->code,
            'discount_percentage' => $this->discountPercentage,
            'message' => 'Promo code ' . $this->code . ' with ' . $this->discountPercentage . '% discount has been removed and is no longer available.',
        ];
    }
}
