<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum FulfillmentType: string
{
    case DELIVERY_BY_UBER = 'DELIVERY_BY_UBER';
    case DELIVERY_BY_MERCHANT = 'DELIVERY_BY_MERCHANT';
    case DINE_IN = 'DINE_IN';
    case PICKUP = 'PICKUP';

    public function description(): string
    {
        return match ($this) {
            self::DELIVERY_BY_UBER => 'Delivered by Uber',
            self::DELIVERY_BY_MERCHANT => 'Delivered by partner\'s couriers Bring Your Own Courier (BYOC)',
            self::DINE_IN => 'dining in',
            self::PICKUP => 'customer picking up the order',
        };
    }
}
