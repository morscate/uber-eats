<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum TaxType: string
{
    case SCHEDULED = 'SCHEDULED';
    case EN_ROUTE_TO_PICKUP = 'EN_ROUTE_TO_PICKUP';
    case ARRIVED_AT_PICKUP = 'ARRIVED_AT_PICKUP';
    case EN_ROUTE_TO_DROPOFF = 'EN_ROUTE_TO_DROPOFF';
    case COMPLETED = 'COMPLETED';
    case FAILED = 'FAILED';
}
