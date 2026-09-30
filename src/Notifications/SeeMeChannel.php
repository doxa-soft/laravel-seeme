<?php

namespace DoxaSoft\LaravelSeeMe\Notifications;

use DoxaSoft\LaravelSeeMe\Contracts\SeeMeServiceInterface;
use DoxaSoft\LaravelSeeMe\Data\SeeMeMessage;
use Illuminate\Notifications\Notification;

class SeeMeChannel
{
    public function __construct(private readonly SeeMeServiceInterface $seeme) {}

    public function send(mixed $notifiable, Notification $notification): void
    {
        $number = $notifiable->routeNotificationFor('seeme', $notification);

        if (empty($number)) {
            return;
        }

        /** @var SeeMeMessage $message */
        $message = $notification->toSeeMe($notifiable);

        $this->seeme->send($number, $message);
    }
}
