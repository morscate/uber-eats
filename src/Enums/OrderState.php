<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum OrderState: string
{
    case CREATED = 'CREATED';
    case OFFERED = 'OFFERED';
    case ACCEPTED = 'ACCEPTED';
    case HANDED_OFF = 'HANDED_OFF';
    case SUCCEEDED = 'SUCCEEDED';
    case FAILED = 'FAILED';
    case UNKNOWN = 'UNKNOWN';

    public function description(): string
    {
        return match ($this) {
            self::CREATED => 'order has been created',
            self::OFFERED => 'order has been offered to the merchant',
            self::ACCEPTED => 'merchant has accepted the order',
            self::HANDED_OFF => 'order has been fully handed off to delivery partners. If there are multiple delivery partners, an order will only transition to this state only when all delivery partners have picked up the order.',
            self::SUCCEEDED => 'order was successfully delivered',
            self::FAILED => 'order failed for any reason',
            self::UNKNOWN => 'catch all for unrecognized states',
        };
    }
}
