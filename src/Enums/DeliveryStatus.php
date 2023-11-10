<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum DeliveryStatus: string
{
    case NIF = 'NIF';
    case CPF = 'CPF';
    case CORPORATE_TAX_ID = 'CORPORATE_TAX_ID';
    case NIP = 'NIP';
}
