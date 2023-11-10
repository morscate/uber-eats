<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum FailureAttributedToParty: string
{
    case UNKNOWN = 'UNKNOWN';
    case MERCHANT = 'MERCHANT';
    case CUSTOMER = 'CUSTOMER';
    case UBER = 'UBER';
    case DELIVERY_PARTNER = 'DELIVERY_PARTNER';
}
