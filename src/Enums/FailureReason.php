<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum FailureReason: string
{
    case POS_DENIED = 'POS_DENIED';
    case ACCEPT_TIMED_OUT = 'ACCEPT_TIMED_OUT';
    case DELIVERY_FAILED = 'DELIVERY_FAILED';
    case CANCELED = 'CANCELED';
    case UNKNOWN = 'UNKNOWN';
}
