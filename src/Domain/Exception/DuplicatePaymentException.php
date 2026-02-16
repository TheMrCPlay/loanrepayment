<?php

namespace App\Domain\Exception;

class DuplicatePaymentException extends \DomainException
{
    protected $message = 'duplicate_payment';
}
