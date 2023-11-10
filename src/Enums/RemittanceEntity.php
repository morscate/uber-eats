<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum RemittanceEntity: string
{
    case UBER = 'UBER';
    case MERCHANT = 'MERCHANT';
    case DELIVERY_PARTNER = 'DELIVERY_PARTNER';
    case CUSTOMER = 'CUSTOMER';
}
