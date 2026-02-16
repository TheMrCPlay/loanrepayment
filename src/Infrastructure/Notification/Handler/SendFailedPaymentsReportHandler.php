<?php

namespace App\Infrastructure\Notification\Handler;

use App\Application\Notification\Message\FailedPaymentsReport;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendFailedPaymentsReportHandler
{
    public function __invoke(FailedPaymentsReport $message): void
    {
        // Failed payments report: support@example.com
    }
}
