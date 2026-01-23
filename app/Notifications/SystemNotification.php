<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
{
    use Queueable;

    public $title;
    public $message;
    public $type; // success, info, warning, danger
    public $link;
    public $requireConfirm;
    public $imageUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $message, $type = 'info', $link = null, $requireConfirm = false, $imageUrl = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->link = $link;
        $this->requireConfirm = (bool) $requireConfirm;
        $this->imageUrl = $imageUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'link' => $this->link,
            'require_confirm' => $this->requireConfirm,
            'ack_status' => null,
            'image_url' => $this->imageUrl
        ];
    }
}
