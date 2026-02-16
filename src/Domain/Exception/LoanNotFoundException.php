<?php

namespace App\Domain\Exception;

class LoanNotFoundException extends \DomainException
{
    public function __construct(string $reference)
    {
        parent::__construct(sprintf('Loan with reference "%s" not found.', $reference));
    }
}
