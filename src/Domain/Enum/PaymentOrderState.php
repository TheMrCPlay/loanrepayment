<?php

namespace App\Domain\Enum;

enum PaymentOrderState: string
{
    case PENDING = 'pending';
    case PROCESSED = 'processed';
    case FAILED = 'failed';
}
