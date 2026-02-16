<?php

namespace App\Application\Notification\Message;

final class FailedPaymentsReport
{
    public function __construct(private readonly array $failures)
    {
    }

    public function getFailures(): array
    {
        return $this->failures;
    }
}
