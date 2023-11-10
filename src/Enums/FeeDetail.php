<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum FeeDetail: string
{
    case SMALL_ORDER_FEE = 'SMALL_ORDER_FEE';
    case DELIVERY_FEE = 'DELIVERY_FEE';
    case PICK_AND_PACK_FEE = 'PICK_AND_PACK_FEE';
    case BAG_FEE = 'BAG_FEE';
}
