<?php

namespace App\Domain\Exception;

class InvalidDateException extends \DomainException
{
    protected $message = 'invalid_date';
}
