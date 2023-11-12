<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum ReasonType: string
{
    case ITEM_ISSUE = 'ITEM_ISSUE';
    case KITCHEN_CLOSED = 'KITCHEN_CLOSED';
    case CUSTOMER_CALLED_TO_CANCEL = 'CUSTOMER_CALLED_TO_CANCEL';
    case RESTAURANT_TOO_BUSY = 'RESTAURANT_TOO_BUSY';
    case ORDER_VALIDATION = 'ORDER_VALIDATION';
    case STORE_CLOSED = 'STORE_CLOSED';
    case TECHNICAL_FAILURE = 'TECHNICAL_FAILURE';
    case POS_NOT_READY = 'POS_NOT_READY';
    case POS_OFFLINE = 'POS_OFFLINE';
    case CAPACITY = 'CAPACITY';
    case ADDRESS = 'ADDRESS';
    case SPECIAL_INSTRUCTIONS = 'SPECIAL_INSTRUCTIONS';
    case PRICING = 'PRICING';
    case UNKNOWN = 'UNKNOWN';
    case OTHER = 'OTHER';

    public function description(): string
    {
        return match ($this) {
            self::ITEM_ISSUE => 'Issue with an item or modifier',
            self::KITCHEN_CLOSED => 'Kitchen is closed',
            self::CUSTOMER_CALLED_TO_CANCEL => 'Customer called to cancel',
            self::RESTAURANT_TOO_BUSY => 'Restaurant is too busy',
            self::ORDER_VALIDATION => 'Order validation error',
            self::STORE_CLOSED => 'Store is closed',
            self::TECHNICAL_FAILURE => 'Technical failure',
            self::POS_NOT_READY => 'POS not ready',
            self::POS_OFFLINE => 'POS is offline',
            self::CAPACITY => 'Store order capacity is full',
            self::ADDRESS => 'Problem with address',
            self::SPECIAL_INSTRUCTIONS => 'Special instructions issue',
            self::PRICING => 'Pricing issues',
            self::UNKNOWN => 'Unknown reason',
            self::OTHER => 'Other',
        };
    }
}
