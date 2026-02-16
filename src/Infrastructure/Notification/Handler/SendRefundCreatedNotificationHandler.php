<?php

namespace App\Infrastructure\Notification\Handler;

use App\Domain\Event\RefundCreated;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendRefundCreatedNotificationHandler
{
    public function __invoke(RefundCreated $event): void
    {
        // Example:
        // Send notification about refund creation
    }
}
