<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum OrderStatus: string
{
    case SCHEDULED = 'SCHEDULED';
    case ACTIVE = 'ACTIVE';
    case COMPLETED = 'COMPLETED';

    public function description(): string
    {
        return match ($this) {
            self::SCHEDULED => 'order is scheduled for a future time',
            self::ACTIVE => 'order is active',
            self::COMPLETED => 'order is completed',
        };
    }
}
