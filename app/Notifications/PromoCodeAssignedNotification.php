<?php

namespace App\Notifications;

use App\Models\PromoCode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PromoCodeAssignedNotification extends Notification
{
    use Queueable;

    protected $promoCode;

    /**
     * Create a new notification instance.
     */
    public function __construct(PromoCode $promoCode)
    {
        $this->promoCode = $promoCode;
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
        $mail = (new MailMessage)
            ->subject('New Promo Code Available!')
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('You have been assigned a new promotional discount code.')
            ->line('**Promo Code:** ' . $this->promoCode->code)
            ->line('**Discount:** ' . $this->promoCode->discount_percentage . '% OFF');

        if ($this->promoCode->description) {
            $mail->line('**Details:** ' . $this->promoCode->description);
        }

        if ($this->promoCode->valid_until) {
            $mail->line('**Valid Until:** ' . $this->promoCode->valid_until->format('F j, Y'));
        }

        if ($this->promoCode->minimum_order_amount) {
            $mail->line('**Minimum Order:** $' . number_format((float)$this->promoCode->minimum_order_amount, 2));
        }

        // Add applicable products information
        $products = $this->promoCode->products;
        $categories = $this->promoCode->categories;

        if ($products->isNotEmpty()) {
            $mail->line('---');
            $mail->line('**Applicable Products:**');
            foreach ($products->take(10) as $product) {
                $productName = $product->title ?? $product->translate('title') ?? 'Product #' . $product->id;
                $mail->line('• ' . $productName);
            }
            if ($products->count() > 10) {
                $mail->line('• ...and ' . ($products->count() - 10) . ' more products');
            }
        }

        if ($categories->isNotEmpty()) {
            $mail->line('---');
            $mail->line('**Applicable Categories:**');
            foreach ($categories->take(10) as $category) {
                $categoryName = $category->title ?? $category->translate('title') ?? 'Category #' . $category->id;
                $mail->line('• ' . $categoryName);
            }
            if ($categories->count() > 10) {
                $mail->line('• ...and ' . ($categories->count() - 10) . ' more categories');
            }
        }

        if ($products->isEmpty() && $categories->isEmpty()) {
            $mail->line('---');
            $mail->line('**This promo code is valid for ALL products!**');
        }

        $mail->action('Shop Now', 'https://www.momtabare.com')
            ->line('Use this code at checkout to get your discount!')
            ->line('Thank you for being a valued customer!');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $products = $this->promoCode->products;
        $categories = $this->promoCode->categories;

        $productNames = $products->map(function ($product) {
            return $product->title ?? $product->translate('title') ?? 'Product #' . $product->id;
        })->toArray();

        $categoryNames = $categories->map(function ($category) {
            return $category->title ?? $category->translate('title') ?? 'Category #' . $category->id;
        })->toArray();

        return [
            'promo_code_id' => $this->promoCode->id,
            'code' => $this->promoCode->code,
            'discount_percentage' => $this->promoCode->discount_percentage,
            'description' => $this->promoCode->description,
            'valid_from' => $this->promoCode->valid_from,
            'valid_until' => $this->promoCode->valid_until,
            'minimum_order_amount' => $this->promoCode->minimum_order_amount,
            'applicable_products' => $productNames,
            'applicable_products_count' => $products->count(),
            'applicable_categories' => $categoryNames,
            'applicable_categories_count' => $categories->count(),
            'applies_to_all_products' => $products->isEmpty() && $categories->isEmpty(),
            'message' => 'You have been assigned a new promo code: ' . $this->promoCode->code . ' with ' . $this->promoCode->discount_percentage . '% discount!',
        ];
    }
}
