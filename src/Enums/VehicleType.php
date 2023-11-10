<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum VehicleType: string
{
    case CAR = 'CAR';
    case BICYCLE = 'BICYCLE';
    case MOTORBIKE = 'MOTORBIKE';
    case PEDESTRIAN = 'PEDESTRIAN';
}
