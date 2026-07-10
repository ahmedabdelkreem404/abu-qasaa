<?php

namespace App\Modules\Payments\Domain\Enums;

enum PaymentProvider: string
{
    case Manual = 'manual';
    case Cod = 'cod';
    case Paymob = 'paymob';
}
