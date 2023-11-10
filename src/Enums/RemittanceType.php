<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum RemittanceType: string
{
    case SUBTOTAL = 'SUBTOTAL';
    case DELIVERY_FEE = 'DELIVERY_FEE';
}
