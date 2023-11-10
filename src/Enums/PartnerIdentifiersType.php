<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum PartnerIdentifiersType: string
{
    case MERCHANT_STORE_ID = 'MERCHANT_STORE_ID';
    case INTEGRATOR_STORE_ID = 'INTEGRATOR_STORE_ID';
    case INTEGRATOR_BRAND_ID = 'INTEGRATOR_BRAND_ID';
    case ORDER_MANAGER_CLIENT_ID = 'ORDER_MANAGER_CLIENT_ID';
}
