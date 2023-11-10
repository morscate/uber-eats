<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum OrderingPlatform: string
{
    case UBER_EATS = 'UBER_EATS';
    case POSTMATES = 'POSTMATES';
}
