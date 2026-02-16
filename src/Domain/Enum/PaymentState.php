<?php

namespace App\Domain\Enum;

enum PaymentState: string
{
    case RECEIVED = 'received';
    case ASSIGNED = 'assigned';
    case PARTIALLY_ASSIGNED = 'partially_assigned';
}
