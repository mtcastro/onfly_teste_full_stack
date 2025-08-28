<?php

namespace App\Notifications;

use App\Models\TravelOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TravelOrderStatusChanged extends Notification
{
    use Queueable;
    private TravelOrder $order;
    public function __construct(TravelOrder $order) {
        $this->order = $order;
    }
    public function via($notifiable): array
    {
        return ['mail'];
    }
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Status do pedido de viagem atualizado')
            ->line('Seu pedido #' . $this->order->id . ' foi atualizado para: ' . strtoupper($this->order->status))
            ->action('Ver pedido', url(env('CLIENT_URL', 'http://localhost:9000') . '?id=' . $this->order->id));
    }
}
