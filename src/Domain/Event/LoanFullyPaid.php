<?php

namespace App\Domain\Event;

final class LoanFullyPaid
{
    public function __construct(
        public readonly string $loanId,
        public readonly string $customerId
    ) {
    }
}
