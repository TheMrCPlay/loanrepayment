<?php

namespace App\Application\Notification;

use Symfony\Component\Messenger\MessageBusInterface;

final class NotificationDispatcher
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function dispatch(array $events): void
    {
        foreach ($events as $event) {
            $this->messageBus->dispatch($event);
        }
    }
}
