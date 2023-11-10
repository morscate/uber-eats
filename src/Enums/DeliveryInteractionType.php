<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum DeliveryInteractionType: string
{
    case DOOR_TO_DOOR = 'DOOR_TO_DOOR';
    case CURBSIDE = 'CURBSIDE';
    case LEAVE_AT_DOOR = 'LEAVE_AT_DOOR';
}
