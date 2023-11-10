<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum InvalidItemType: string
{
    case NOT_ON_MENU = 'NOT_ON_MENU';
    case UNAVAILABLE = 'UNAVAILABLE';
    case MISSING_INFO = 'MISSING_INFO';
    case PRICING = 'PRICING';
    case QUANTITY = 'QUANTITY';
    case OUT_OF_ITEM = 'OUT_OF_ITEM';
    case OTHER = 'OTHER';
}
