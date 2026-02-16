<?php

namespace App\Infrastructure\Notification\Handler;

use App\Domain\Event\PaymentReceived;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendPaymentReceivedNotificationHandler
{
    public function __invoke(PaymentReceived $event)
    {
        // Email/SMS
    }
}
