<?php

namespace App\Domain\Enum;

enum LoanState: string
{
    case ACTIVE = 'ACTIVE';
    case PAID = 'PAID';
}
