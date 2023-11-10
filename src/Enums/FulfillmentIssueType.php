<?php

declare(strict_types=1);

namespace Morscate\UberEats\Enums;

enum FulfillmentIssueType: string
{
    case OUT_OF_ITEM = 'OUT_OF_ITEM';
    case PARTIAL_AVAILABILITY = 'PARTIAL_AVAILABILITY';
    case OUT_OF_MODIFIER_OPTION = 'OUT_OF_MODIFIER_OPTION';
    case CANNOT_FULFILL_ALLERGY_REQUEST = 'CANNOT_FULFILL_ALLERGY_REQUEST';
    case CANNOT_FULFILL_MERCHANT_INSTRUCTION = 'CANNOT_FULFILL_MERCHANT_INSTRUCTION';
}
