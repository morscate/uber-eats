<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum PreparationStatus: string
{
    case PREPARING = 'PREPARING';
    case OUT_OF_ITEM_PENDING_CUSTOMER_RESPONSE = 'OUT_OF_ITEM_PENDING_CUSTOMER_RESPONSE';
    case READY_FOR_HANDOFF = 'READY_FOR_HANDOFF';

    public function description(): string
    {
        return match ($this) {
            self::PREPARING => 'order is being prepared',
            self::OUT_OF_ITEM_PENDING_CUSTOMER_RESPONSE => 'merchant has indicated out of item and the order is now waiting on customer response',
            self::READY_FOR_HANDOFF => 'order is ready to be handed off',
        };
    }
}
