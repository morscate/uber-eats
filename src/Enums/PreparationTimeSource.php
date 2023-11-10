<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum PreparationTimeSource: string
{
    case PREDICTED_BY_UBER = 'PREDICTED_BY_UBER';
    case DEFAULT = 'DEFAULT';
    case PROVIDED_BY_MERCHANT = 'PROVIDED_BY_MERCHANT';
}
