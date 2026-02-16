<?php

namespace App\Infrastructure\Notification\Handler;

use App\Domain\Event\LoanFullyPaid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendLoanFullyPaidNotificationHandler
{
    public function __invoke(LoanFullyPaid $event): void
    {
        // Example:
        // $this->mailer->send(...)
        // or SMS service
    }
}
