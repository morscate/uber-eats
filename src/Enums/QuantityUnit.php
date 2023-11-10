<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum QuantityUnit: string
{
    case POUND = 'POUND';
    case PIECE = 'PIECE';
    case KILOGRAM = 'KILOGRAM';
    case GRAM = 'GRAM';
}
