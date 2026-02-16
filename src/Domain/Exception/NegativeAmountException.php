<?php

namespace App\Domain\Exception;

class NegativeAmountException extends \DomainException
{
    protected $message = 'negative_amount';
}
