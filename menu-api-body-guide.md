# Uber Eats Menu API Body Guide

This document explains how to build the menu payload used to create or update a store menu, based only on the schema details provided.

It also explains the structure returned by:

- `POST /v2/eats/stores/{store_id}/menus`

Important:

- `POST /menus` does not send a request body.

## 1. Endpoint Summary

### Get Menu

- Method: `P0ST`
- URL: `https://api.uber.com/v2/eats/stores/{store_id}/menus`
- Path parameter:
  - `store_id` - required - unique identifier representing a store
- Query parameter:
  - `menu_type` - optional
  - Allowed values:
    - `MENU_TYPE_FULFILLMENT_DELIVERY`
    - `MENU_TYPE_FULFILLMENT_PICK_UP`
    - `MENU_TYPE_FULFILLMENT_DINE_IN`
  - Default if omitted:
    - `MENU_TYPE_FULFILLMENT_DELIVERY`
- Headers:
  - `Authorization: Bearer <token>` - required
  - `Accept-Encoding: gzip` - optional, recommended

### What this endpoint returns

The response body is a root `MenuConfiguration` object containing:

- `menus`
- `categories`
- `items`
- `modifier_groups`

## 2. Root Object: MenuConfiguration

This is the main object you should think in when building a full menu.

```json
{
  "menus": [],
  "categories": [],
  "items": [],
  "modifier_groups": []
}
```

Fields:

- `menus` - required - `Menu[]`
- `categories` - required - `Category[]`
- `items` - required - `Item[]`
- `modifier_groups` - required - `ModifierGroup[]`

## 3. Build Order

The cleanest way to build the body is in this order:

1. Create all `items`
2. Create any `modifier_groups`
3. Create `categories` that reference items or modifier groups through `entities`
4. Create `menus` that reference categories through `category_ids`
5. Wrap everything inside the root `MenuConfiguration`

## 4. Minimal Shape

This is the smallest useful mental model:

```json
{
  "menus": [
    {
      "id": "menu_main",
      "title": {
        "translations": {
          "en_us": "Main Menu"
        }
      },
      "service_availability": [
        {
          "day_of_week": "monday",
          "time_periods": [
            {
              "start_time": "09:00",
              "end_time": "23:59"
            }
          ]
        }
      ],
      "category_ids": [
        "cat_burgers"
      ]
    }
  ],
  "categories": [
    {
      "id": "cat_burgers",
      "title": {
        "translations": {
          "en_us": "Burgers"
        }
      },
      "entities": [
        {
          "id": "item_burger_1",
          "type": "ITEM"
        }
      ]
    }
  ],
  "items": [
    {
      "id": "item_burger_1",
      "title": {
        "translations": {
          "en_us": "Cheese Burger"
        }
      },
      "price_info": {
        "price": 1299
      },
      "tax_info": {}
    }
  ],
  "modifier_groups": []
}
```

## 5. Object Reference

## 5.1 Menu

A `Menu` is a collection of categories that is active during certain hours.

Fields:

- `id` - required - string
- `title` - required - `MultiLanguageText`
- `subtitle` - optional - `MultiLanguageText`
- `service_availablity` / `service_availability` - required in meaning from the provided schema - `ServiceAvailability[]`
- `category_ids` - required - `string[]`

Notes:

- The menu only references categories by ID.
- Every category ID listed here should exist in `categories`.

Example:

```json
{
  "id": "menu_lunch",
  "title": {
    "translations": {
      "en_us": "Lunch"
    }
  },
  "subtitle": {
    "translations": {
      "en_us": "Served daily"
    }
  },
  "service_availability": [
    {
      "day_of_week": "monday",
      "time_periods": [
        {
          "start_time": "11:00",
          "end_time": "15:00"
        }
      ]
    }
  ],
  "category_ids": [
    "cat_burgers",
    "cat_drinks"
  ]
}
```

## 5.2 MultiLanguageText

Used for titles, subtitles, descriptions, and similar text.

Fields:

- `translations` - required - object

Rules:

- One translation must be provided.
- Locale codes should include language and country, for example `en_us`.

Example:

```json
{
  "translations": {
    "en_us": "Loaded Fries"
  }
}
```

## 5.3 ServiceAvailability

Defines the day and time windows when a menu is active.

Fields:

- `day_of_week` - required - enum
- `time_periods` - required - `TimePeriod[]`

Allowed `day_of_week` values:

- `monday`
- `tuesday`
- `wednesday`
- `thursday`
- `friday`
- `saturday`
- `sunday`

Example:

```json
{
  "day_of_week": "friday",
  "time_periods": [
    {
      "start_time": "10:00",
      "end_time": "23:59"
    }
  ]
}
```

## 5.4 TimePeriod

Continuous time span on an individual day.

Fields:

- `start_time` - required - string - `HH:MM`
- `end_time` - required - string - `HH:MM`

Example:

```json
{
  "start_time": "08:30",
  "end_time": "23:00"
}
```

## 5.5 Category

A category groups top-level things shown together on the menu.

Fields:

- `id` - required - string
- `title` - required - `MultiLanguageText`
- `subtitle` - optional - `MultiLanguageText`
- `entities` - required - `MenuEntity[]`

Rules:

- Top-level saleable items inside a category must be listed in `entities`.
- The provided schema states all category entities must be of type `ITEM`.

Example:

```json
{
  "id": "cat_sides",
  "title": {
    "translations": {
      "en_us": "Sides"
    }
  },
  "subtitle": {
    "translations": {
      "en_us": "Snacks and extras"
    }
  },
  "entities": [
    {
      "id": "item_loaded_fries",
      "type": "ITEM"
    }
  ]
}
```

## 5.6 MenuEntity

Used inside categories and modifier groups to point to another object by ID.

Fields:

- `id` - required - string
- `type` - required - enum

Allowed `type` values:

- `ITEM`
- `MODIFIER_GROUP`

Important:

- For category `entities`, the provided schema says the entities must be `ITEM`.
- For modifier groups, `modifier_options` are also menu entities and must be `ITEM`.

Example:

```json
{
  "id": "item_coke",
  "type": "ITEM"
}
```

## 5.7 Item

An `Item` is the main product object.

Fields:

- `id` - required - string
- `external_data` - optional - string
- `title` - required - `MultiLanguageText`
- `description` - optional - `MultiLanguageText`
- `image_url` - optional - string
- `price_info` - required - `PriceRules`
- `quantity_info` - optional - `QuantityConstraintRules`
- `suspension_info` - optional - `SuspensionRules`
- `modifier_group_ids` - optional - `ModifierGroupsRules`
- `tax_info` - required - `TaxInfo`
- `nutritional_info` - optional - `NutritionalInfo`
- `dish_info` - optional - `DishInfo`
- `visibility_info` - optional - `VisibilityInfo`
- `tax_label_info` - optional - `TaxLabelsRuleSet`
- `product_info` - optional - `ProductInfo`
- `bundled_items` - optional - `BundledItems[]`
- `beverage_info` - optional - `BeverageInfo`
- `physical_properities_info` - optional - `PhysicalPropertiesInfo`
- `medication_info` - optional - `PhysicalPropertiesInfo` / medication style object as provided
- `selling_info` - optional - `SellingInfo`

Image rules for `image_url`:

- File size under 25MB
- JPG, WEBP, or PNG
- Width between 320px and 6000px
- Height between 320px and 6000px

Price rules:

- Price must be an integer
- Price is in the lowest currency denomination
- Example: cents for USD
- Price should always be set, even when the value is `0`

Example:

```json
{
  "id": "item_loaded_fries",
  "external_data": "pos-item-204",
  "title": {
    "translations": {
      "en_us": "Loaded Fries"
    }
  },
  "description": {
    "translations": {
      "en_us": "Crispy fries topped with cheese sauce and spring onions"
    }
  },
  "image_url": "https://example.com/fries.png",
  "price_info": {
    "price": 4500,
    "overrides": []
  },
  "tax_info": {},
  "dish_info": {
    "classifications": {
      "ingredients": null,
      "additives": null
    }
  },
  "product_info": {
    "product_traits": null,
    "countries_of_origin": null
  },
  "bundled_items": null
}
```

## 5.8 PriceRules

Defines pricing for an item.

Fields:

- `price` - required - int
- `in_store_price` - optional - int
- `in_store_discounted_price` - optional - int
- `core_price` - optional - int
- `container_deposit` - optional - int
- `overrides` - optional - `PriceOverride[]`
- `priced_by_unit` - optional - `MeasurementUnit`

Rules:

- `core_price` must be greater than or equal to `price`
- `container_deposit` is metadata only and does not change order price

Example:

```json
{
  "price": 1299,
  "in_store_price": 1199,
  "in_store_discounted_price": 1099,
  "core_price": 1299,
  "container_deposit": 0,
  "overrides": []
}
```

## 5.9 PriceOverride

Overrides the base price in a specific context.

Fields:

- `context_type` - required - enum
- `context_value` - required - string
- `price` - required - int
- `core_price` - optional - int

Allowed `context_type` values:

- `MENU`
- `ITEM`
- `MODIFIER_GROUP`

Example:

```json
{
  "context_type": "MENU",
  "context_value": "menu_lunch",
  "price": 1199,
  "core_price": 1299
}
```

## 5.10 QuantityConstraintRules

Used when quantity must be controlled.

Fields:

- `quantity` - required - `QuantityConstraint`
- `overrides` - optional - `QuantityConstraintOverride[]`

## 5.11 QuantityConstraint

Defines quantity limits.

Fields:

- `min_permitted` - optional - int
- `max_permitted` - optional - int
- `is_min_permitted_optional` - optional - bool
- `charge_above` - optional - int
- `refund_under` - optional - int
- `min_permitted_unique` - optional - int
- `max_permitted_unique` - optional - int

Rules:

- `min_permitted` cannot be negative
- `max_permitted` cannot be less than `min_permitted`
- `charge_above` and `refund_under` must both be null or both be non-null
- `charge_above` cannot be negative
- `refund_under` cannot be negative
- `min_permitted_unique` cannot be negative
- `max_permitted_unique` cannot be less than `min_permitted_unique`
- `min_permitted_unique` and `max_permitted_unique` apply only to modifier groups

Example:

```json
{
  "min_permitted": 0,
  "max_permitted": 3,
  "is_min_permitted_optional": true
}
```

## 5.12 QuantityConstraintOverride

Fields:

- `context_type` - required - enum
- `context_value` - required - string
- `quantity` - required - `QuantityConstraint`

Allowed `context_type` values:

- `MENU`
- `ITEM`
- `MODIFIER_GROUP`

## 5.13 SuspensionRules

Fields:

- `suspension` - optional - `Suspension`
- `overrides` - optional - `SuspensionOverride[]`

## 5.14 Suspension

Fields:

- `suspend_until` - optional - int - Unix timestamp in seconds
- `reason` - optional - string

Rule:

- A null value or a past timestamp means the item is available

Example:

```json
{
  "suspend_until": 1767225600,
  "reason": "Out of stock"
}
```

## 5.15 SuspensionOverride

Fields:

- `context_type` - required - enum
- `context_value` - required - string
- `suspension` - required - `Suspension`

Allowed `context_type` values:

- `MENU`
- `ITEM`
- `MODIFIER_GROUP`

## 5.16 ModifierGroupsRules

Fields:

- `ids` - required - `string[]`
- `overrides` - optional - `ModifierGroupsOverride[]`

Example:

```json
{
  "ids": [
    "mod_cheese",
    "mod_sauces"
  ],
  "overrides": []
}
```

## 5.17 ModifierGroupsOverride

Fields:

- `context_type` - required - enum
- `context_value` - required - string
- `ids` - required - `string[]`

Allowed `context_type` values:

- `MENU`
- `ITEM`
- `MODIFIER_GROUP`

## 5.18 TaxInfo

Fields:

- `tax_rate` - optional - float between `0.0` and `100.0`
- `vat_rate_percentage` - optional - float between `0.0` and `100.0`

Use cases:

- `tax_rate` when tax is added on top of item price
- `vat_rate_percentage` when tax is already included in the item price

Example:

```json
{
  "vat_rate_percentage": 15.0
}
```

## 5.19 NutritionalInfo

All fields below are optional:

- `calories` - `EnergyInfo`
- `kilojoules` - `EnergyInfo`
- `serving_size` - `MeasurementInterval`
- `number_of_servings` - int
- `number_of_servings_interval` - `Interval`
- `net_quantity` - `MeasurementInterval`
- `calories_per_serving` - `EnergyInfo`
- `kilojoules_per_serving` - `EnergyInfo`
- `fat` - `NutrientInfo`
- `saturated_fatty_acids` - `NutrientInfo`
- `carbohydrates` - `NutrientInfo`
- `sugar` - `NutrientInfo`
- `protein` - `NutrientInfo`
- `salt` - `NutrientInfo`
- `allergens` - `string[]`

## 5.20 MeasurementInterval

Fields:

- `measurement_type` - required - string
- `weight_interval` - required when type is weight - `WeightInterval`
- `volume_interval` - required when type is volume - `VolumeInterval`
- `count_interval` - required when type is count - `CountInterval`

Allowed values stated:

- `MEASUREMENT_TYPE_WEIGHT`
- `MEASUREMENT_TYPE_VOLUME`
- `MEASUREMENT_TYPE_COUNT`

## 5.21 Interval

Fields:

- `lower` - required - int
- `upper` - optional - int

Note:

- If `lower` equals `upper`, it behaves like a single value

## 5.22 NutrientInfo

Fields:

- `amount` - required - `WeightInterval`

## 5.23 WeightInterval

Fields:

- `interval` - required - `Interval`
- `weight` - required - `Weight`

## 5.24 VolumeInterval

Fields:

- `interval` - required - `Interval`
- `volume` - required - `Volume`

## 5.25 CountInterval

Fields:

- `interval` - required - `Interval`
- `count` - required - `Count`

## 5.26 Weight

Fields:

- `unit_type` - required - string

Allowed values:

- `WEIGHT_UNIT_TYPE_METRIC_GRAM`
- `WEIGHT_UNIT_TYPE_METRIC_MICROGRAM`
- `WEIGHT_UNIT_TYPE_METRIC_MILLIGRAM`
- `WEIGHT_UNIT_TYPE_METRIC_KILOGRAM`
- `WEIGHT_UNIT_TYPE_METRIC_TON`
- `WEIGHT_UNIT_TYPE_IMPERIAL_AVOIRDUPOIS_OUNCE`
- `WEIGHT_UNIT_TYPE_IMPERIAL_AVOIRDUPOIS_POUND`

## 5.27 Volume

Fields:

- `unit_type` - required - string

Allowed values:

- `VOLUME_UNIT_TYPE_METRIC_LITER`
- `VOLUME_UNIT_TYPE_METRIC_MILLILITER`
- `VOLUME_UNIT_TYPE_IMPERIAL_FLUID_OUNCE`
- `VOLUME_UNIT_TYPE_IMPERIAL_PINT`
- `VOLUME_UNIT_TYPE_IMPERIAL_GALLON`
- `VOLUME_UNIT_TYPE_IMPERIAL_QUART`
- `VOLUME_UNIT_TYPE_IMPERIAL_CUP`
- `VOLUME_UNIT_TYPE_IMPERIAL_TABLESPOON`
- `VOLUME_UNIT_TYPE_IMPERIAL_TEASPOON`

## 5.28 Count

Fields:

- `unit_type` - required - string
- `custom_unit` - required only when unit type is custom - string

Allowed values:

- `COUNT_UNIT_TYPE_CUSTOM`
- `COUNT_UNIT_TYPE_PIECE`
- `COUNT_UNIT_TYPE_SLICE`
- `COUNT_UNIT_TYPE_TABLET`
- `COUNT_UNIT_TYPE_CAPSULE`

## 5.29 EnergyInfo

Fields:

- `energy_interval` - required in this structure description - `Interval`
- `lower_range` - optional, deprecated - int
- `upper_range` - optional, deprecated - int
- `display_type` - required when used - string

Allowed `display_type` values:

- `single_item`
- `double_items`
- `additive_item`
- `multiple_items`

## 5.30 DishInfo

Fields:

- `classifications` - optional - `Classifications`

## 5.31 Classifications

All fields below are optional unless noted by a nested dependency:

- `can_serve_alone` - boolean
- `is_vegetarian` - boolean
- `alcoholic_items` - int
- `dietary_label_info` - `DietaryLabelInfo`
- `instructions_for_use` - string
- `ingredients` - `string[]`
- `additives` - `string[]`
- `preparation_type` - string
- `food_business_operator` - `FoodBusinessOperator`
- `is_high_fat_salt_sugar` - boolean

Rules:

- `alcoholic_items > 0` indicates alcoholic content count
- `null` or `0` means non-alcoholic
- `ingredients` max 50 values
- `instructions_for_use` max 200 characters
- `preparation_type` must be `PREPACKAGED` or empty

## 5.32 FoodBusinessOperator

Fields:

- `name` - required - string
- `address` - required - string

## 5.33 DietaryLabelInfo

Fields:

- `labels` - optional in the provided response section - `string[]`

Allowed labels:

- `VEGAN`
- `VEGETARIAN`
- `GLUTEN_FREE`

## 5.34 VisibilityInfo

Fields:

- `hours` - required - `VisibilityHours`

## 5.35 VisibilityHours

Fields:

- `start_date` - optional - string - ISO 8601 date
- `end_date` - optional - string - ISO 8601 date
- `hours_of_week` - required - `HoursOfWeek`

## 5.36 HoursOfWeek

Fields:

- `day_of_week` - required - enum
- `time_periods` - required - `TimePeriod[]`

Allowed values:

- `monday`
- `tuesday`
- `wednesday`
- `thursday`
- `friday`
- `saturday`
- `sunday`

## 5.37 ModifierGroup

A modifier group is used when an item can be customized.

Fields:

- `id` - required - string
- `external_data` - optional - string
- `title` - required - `MultiLanguageText`
- `quantity_info` - optional - `QuantityConstraintRules`
- `modifier_options` - required - `MenuEntity[]`
- `display_type` - optional - enum

Allowed `display_type` values:

- `expanded`
- `collapsed`

Rules:

- `modifier_options` must be menu entities of type `ITEM`

Example:

```json
{
  "id": "mod_sauces",
  "title": {
    "translations": {
      "en_us": "Choose a sauce"
    }
  },
  "modifier_options": [
    {
      "id": "item_sauce_bbq",
      "type": "ITEM"
    }
  ],
  "display_type": "expanded"
}
```

## 5.38 TaxLabelsRuleSet

Fields:

- `default_value` - required - `TaxLabelsInfo`

## 5.39 TaxLabelsInfo

Fields:

- `labels` - required - `string[]`
- `source` - required - string

Rules:

- Category and temperature labels are required together when this object is used
- `source` must be `MANUAL`

Example:

```json
{
  "default_value": {
    "labels": [
      "CAT_PREPACKAGED_FOOD",
      "CAT_SNACK",
      "TEMP_UNHEATED"
    ],
    "source": "MANUAL"
  }
}
```

## 5.40 TaxLabels

The provided schema includes a large list of allowed tax labels and combinations. When using `tax_label_info`, select labels exactly from the allowed values supplied by Uber.

Examples from the provided schema include:

- `CAT_PREPARED_FOOD`
- `CAT_DELI_PLATTER`
- `CAT_FOOD_BY_WT_VOL`
- `CAT_SANDWICH`
- `CAT_ICECREAM`
- `CAT_PREPACKAGED_FOOD`
- `CAT_SNACK`
- `CAT_CANDY`
- `CAT_ALCOHOL`
- `CAT_JUICE`
- `CAT_WATER`
- `CAT_SOFT_DRINK`
- `CAT_TEA`
- `CAT_COFFEE`
- `CAT_MILK_COCOA`
- `TEMP_HEATED`
- `TEMP_UNHEATED`
- `TEMP_COLD`

If you use tax labels, match the classification and temperature requirements exactly.

## 5.41 ProductInfo

All fields are optional:

- `target_market` - integer
- `gtin` - string
- `plu` - string
- `merchant_id` - string
- `product_type` - string
- `product_traits` - `string[]`
- `countries_of_origin` - `string[]`

## 5.42 BundledItems

Fields:

- `item_id` - required - string
- `core_price` - required - int
- `included_quantity` - required - int

Used for items always included as part of another item.

Example:

```json
{
  "item_id": "item_fries_small",
  "core_price": 300,
  "included_quantity": 1
}
```

## 5.43 PhysicalPropertiesInfo

Fields:

- `reusable_packaging` - required when object is used - boolean

## 5.44 BeverageInfo

Fields:

- `caffeine_amount` - optional - integer
- `alcohol_by_volume` - optional - int in E2 format
- `coffee_info` - optional - `CoffeeInfo`

## 5.45 CoffeeInfo

Fields:

- `coffee_bean_origin` - optional - `string[]`

## 5.46 MedicationInfo

Fields:

- `medical_prescription_required` - optional - boolean

## 5.47 SellingInfo

Fields:

- `selling_options` - required when object is used - `SellingOption[]`

## 5.48 SellingOption

Fields:

- `sold_by_unit` - optional - `MeasurementUnit`
- `quantity_constraints` - optional - `SellingQuantityConstraint`
- `priced_by_to_sold_by_unit_conversion_info` - optional - `PricedByToSoldByUnitConversionInfo`

## 5.49 MeasurementUnit

Fields:

- `measurement_type` - required - enum
- `length_unit` - optional, required only if length type is used - enum
- `weight_unit` - optional, required only if weight type is used - enum
- `volume_unit` - optional, required only if volume type is used - enum

Allowed `measurement_type` values:

- `MEASUREMENT_TYPE_COUNT`
- `MEASUREMENT_TYPE_WEIGHT`
- `MEASUREMENT_TYPE_VOLUME`
- `MEASUREMENT_TYPE_LENGTH`

Allowed `length_unit` values:

- `LENGTH_UNIT_TYPE_METRIC_METER`
- `LENGTH_UNIT_TYPE_METRIC_MILLIMETER`
- `LENGTH_UNIT_TYPE_METRIC_CENTIMETER`

Allowed `weight_unit` values:

- `WEIGHT_UNIT_TYPE_METRIC_KILOGRAM`
- `WEIGHT_UNIT_TYPE_METRIC_GRAM`
- `WEIGHT_UNIT_TYPE_METRIC_MILLIGRAM`
- `WEIGHT_UNIT_TYPE_IMPERIAL_POUND`
- `WEIGHT_UNIT_TYPE_IMPERIAL_OUNCE`

Allowed `volume_unit` values:

- `VOLUME_UNIT_TYPE_US_FLUID_OUNCE`
- `VOLUME_UNIT_TYPE_METRIC_LITER`
- `VOLUME_UNIT_TYPE_METRIC_MILLILITER`

## 5.50 SellingQuantityConstraint

All fields are optional:

- `min_permitted` - float
- `max_permitted` - float
- `increment` - float
- `default_quantity` - float

Precision rule:

- Up to 5 decimal places after the decimal point

## 5.51 PricedByToSoldByUnitConversionInfo

Fields:

- `conversion_rate` - optional - float

Rule:

- Up to 5 decimal places after the decimal point
- Usage: `priced_by quantity = sold_by quantity * conversionRate`

## 6. Example: Full Body

This example shows a realistic full body using only the structures described above.

```json
{
  "menus": [
    {
      "id": "menu_main",
      "title": {
        "translations": {
          "en_us": "Main Menu"
        }
      },
      "subtitle": {
        "translations": {
          "en_us": "All day"
        }
      },
      "service_availability": [
        {
          "day_of_week": "monday",
          "time_periods": [
            {
              "start_time": "00:00",
              "end_time": "23:59"
            }
          ]
        },
        {
          "day_of_week": "tuesday",
          "time_periods": [
            {
              "start_time": "00:00",
              "end_time": "23:59"
            }
          ]
        }
      ],
      "category_ids": [
        "cat_burgers",
        "cat_drinks"
      ]
    }
  ],
  "categories": [
    {
      "id": "cat_burgers",
      "title": {
        "translations": {
          "en_us": "Burgers"
        }
      },
      "subtitle": {
        "translations": {
          "en_us": "Signature burgers"
        }
      },
      "entities": [
        {
          "id": "item_burger_1",
          "type": "ITEM"
        }
      ]
    },
    {
      "id": "cat_drinks",
      "title": {
        "translations": {
          "en_us": "Drinks"
        }
      },
      "entities": [
        {
          "id": "item_drink_1",
          "type": "ITEM"
        }
      ]
    }
  ],
  "items": [
    {
      "id": "item_burger_1",
      "external_data": "POS-1001",
      "title": {
        "translations": {
          "en_us": "Classic Burger"
        }
      },
      "description": {
        "translations": {
          "en_us": "Beef patty, cheese, pickles, and house sauce"
        }
      },
      "image_url": "https://example.com/burger.png",
      "price_info": {
        "price": 1299,
        "overrides": []
      },
      "modifier_group_ids": {
        "ids": [
          "mod_sauces"
        ],
        "overrides": []
      },
      "tax_info": {
        "vat_rate_percentage": 15.0
      },
      "dish_info": {
        "classifications": {
          "ingredients": [
            "beef",
            "cheese",
            "pickle"
          ],
          "additives": []
        }
      },
      "product_info": {
        "merchant_id": "POS-1001"
      },
      "bundled_items": []
    },
    {
      "id": "item_drink_1",
      "title": {
        "translations": {
          "en_us": "Iced Tea"
        }
      },
      "price_info": {
        "price": 299,
        "overrides": []
      },
      "tax_info": {},
      "beverage_info": {
        "caffeine_amount": 35
      }
    },
    {
      "id": "item_sauce_bbq",
      "title": {
        "translations": {
          "en_us": "BBQ Sauce"
        }
      },
      "price_info": {
        "price": 0,
        "overrides": []
      },
      "tax_info": {}
    }
  ],
  "modifier_groups": [
    {
      "id": "mod_sauces",
      "title": {
        "translations": {
          "en_us": "Choose a sauce"
        }
      },
      "modifier_options": [
        {
          "id": "item_sauce_bbq",
          "type": "ITEM"
        }
      ],
      "display_type": "expanded"
    }
  ]
}
```

## 7. Validation Checklist

Before sending the body, verify all of the following:

1. Root object includes `menus`, `categories`, `items`, and `modifier_groups`
2. Every `menu.category_ids[]` value exists in `categories`
3. Every `category.entities[].id` exists in `items` or matches the intended entity type
4. Category entity types are `ITEM`
5. Every item has:
   - `id`
   - `title`
   - `price_info.price`
   - `tax_info`
6. All price values are integers
7. Locale keys are full locale codes such as `en_us`
8. Time values use `HH:MM`
9. Enum values match Uber’s allowed values exactly
10. Optional nested objects are only included when they contain valid content

## 8. Practical Notes

- Use `GET /menus` when you need the full menu currently stored for a shop.
- Use gzip in the response headers when large payload size is a concern.
- The body can grow very large once categories, modifiers, nutrition, tax labels, and selling info are included.
- Keep IDs stable. Categories, items, modifier groups, and menus all reference each other by ID.
- If you use nested optional objects, validate their internal required fields before sending.

## 9. Quick Required vs Optional Summary

### Root

- `menus` - required
- `categories` - required
- `items` - required
- `modifier_groups` - required

### Menu

- `id` - required
- `title` - required
- `subtitle` - optional
- `service_availability` - required
- `category_ids` - required

### Category

- `id` - required
- `title` - required
- `subtitle` - optional
- `entities` - required

### MenuEntity

- `id` - required
- `type` - required

### Item

- `id` - required
- `external_data` - optional
- `title` - required
- `description` - optional
- `image_url` - optional
- `price_info` - required
- `quantity_info` - optional
- `suspension_info` - optional
- `modifier_group_ids` - optional
- `tax_info` - required
- `nutritional_info` - optional
- `dish_info` - optional
- `visibility_info` - optional
- `tax_label_info` - optional
- `product_info` - optional
- `bundled_items` - optional
- `beverage_info` - optional
- `physical_properities_info` - optional
- `medication_info` - optional
- `selling_info` - optional

### ModifierGroup

- `id` - required
- `external_data` - optional
- `title` - required
- `quantity_info` - optional
- `modifier_options` - required
- `display_type` - optional

This file can be used as a field-by-field reference when constructing or reviewing a menu body.

## 10. Complete Tax Label Appendix

The following appendix reproduces the provided tax classification values so nothing from the supplied reference is omitted.

### Tax classification entries from the provided details

- Unheated Prepared Food: `CAT_PREPARED_FOOD` + `TEMP_UNHEATED`
- Unheated Deli Platter: `CAT_DELI_PLATTER` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Unheated Food Sold by Weight/Volume: `CAT_FOOD_BY_WT_VOL` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Unheated Sandwich/Wrap: `CAT_SANDWICH` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Ice Cream (hand scooped): `CAT_ICECREAM` + `TEMP_COLD`
- Pre-Packaged Food: `CAT_PREPACKAGED_FOOD` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Pre-Packaged Ice Cream: `CAT_PREPACKAGED_FOOD`, `CAT_ICECREAM` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Pre-Packaged Snack: `CAT_PREPACKAGED_FOOD`, `CAT_SNACK` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Candy: `CAT_CANDY` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Candy Flour: `CAT_CANDY`, `TRAIT_FLOUR` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Alcohol: `CAT_ALCOHOL` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- 100% Juice: `CAT_JUICE`, `TRAIT_PCT_100` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- 70% - 99% Juice: `CAT_JUICE`, `TRAIT_PCT_70TO99` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- 50% - 69% Juice: `CAT_JUICE`, `TRAIT_PCT_50TO69` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- 25% - 49% Juice: `CAT_JUICE`, `TRAIT_PCT_25TO49` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- 5% - 24% Juice: `CAT_JUICE`, `TRAIT_PCT_5TO24` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- 1% - 4% Juice: `CAT_JUICE`, `TRAIT_PCT_1TO4` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Noncarbonated, unflavored/unsweetened water: `CAT_WATER`, `TRAIT_NONCARB`, `TRAIT_UNFLV_UNSWT`, `CONTAINER_BOTTLED` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Noncarbonated, flavored/sweetened water: `CAT_WATER`, `TRAIT_NONCARB`, `TRAIT_FLV_SWT`, `CONTAINER_BOTTLED` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Carbonated, unflavored/unsweetened water: `CAT_WATER`, `TRAIT_CARB`, `TRAIT_UNFLV_UNSWT`, `CONTAINER_BOTTLED` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Carbonated, flavored/sweetened water: `CAT_WATER`, `TRAIT_CARB`, `TRAIT_FLV_SWT`, `CONTAINER_BOTTLED` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Soft drink, bottled: `CAT_SOFT_DRINK`, `CONTAINER_BOTTLED` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Soft drink, noncarbonated and bottled: `CAT_SOFT_DRINK`, `TRAIT_NONCARB`, `CONTAINER_BOTTLED` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Powdered bottled drink: `CAT_POWDERED_DRINK`, `CONTAINER_BOTTLED` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Energy bottled drink: `CAT_ENERGY_DRINK`, `CONTAINER_BOTTLED` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Prepared drink: `CAT_PREPARED_DRINK` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Bottled tea: `CAT_TEA`, `CONTAINER_BOTTLED` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Bottled coffee: `CAT_COFFEE`, `CONTAINER_BOTTLED` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Bottled milk cocoa: `CAT_MILK_COCOA`, `CONTAINER_BOTTLED` + `TEMP_HEATED` or `TEMP_UNHEATED` or `TEMP_COLD`
- Sporting Activities Clothing/Equipment: `CAT_SPORTING_CLOTHING` + optional temperature label if provided
- Bathing Suits: `CAT_BATHING_SUITS` + optional temperature label if provided
- Other Clothing: `CAT_CLOTHING` + optional temperature label if provided
- Costumes: `CAT_COSTUMES` + optional temperature label if provided
- Disposable Glove: `CAT_DISPOSABLE_GLOVES` + optional temperature label if provided
- Protective/Safety Clothing: `CAT_PROTECTIVE_CLOTHING` + optional temperature label if provided
- Footwear and Accessories: `CAT_FOOTWEAR` + optional temperature label if provided
- Computer Hardware: `CAT_COMP_HARDWARE` + optional temperature label if provided
- Batteries: `CAT_BATTERIES` + optional temperature label if provided
- Office/School Supplies: `CAT_SCHOOL_SUPPLIES` + optional temperature label if provided
- Infant Diapers: `CAT_DIAPERS` + optional temperature label if provided
- Baby Wipes: `CAT_BABY_WIPES` + optional temperature label if provided
- Pacifiers: `CAT_PACIFIERS` + optional temperature label if provided
- Baby Formula: `CAT_BABY_FORMULA` + optional temperature label if provided
- Condoms: `CAT_CONDOMS` + optional temperature label if provided
- Personal Lubricants: `CAT_PERSONAL_LUBRICANTS` + optional temperature label if provided
- Pregnancy Tests: `CAT_PREGNANCY_TEST` + optional temperature label if provided
- Miscellaneous Equipment, Devices Sold Under Prescription: `CAT_PRESCRIPTION_DEVICES` + optional temperature label if provided
- Prescription Drugs: `CAT_PRESCRIPTION_DRUGS` + optional temperature label if provided
- OTC Medications: `CAT_OTC_MEDICATION` + optional temperature label if provided
- First Aid Kits: `CAT_FIRST_AID_KITS` + optional temperature label if provided
- Bandages: `CAT_BANDAGES` + optional temperature label if provided
- Petroleum Jelly: `CAT_PETROLEUM_JELLY` + optional temperature label if provided
- Medicated Items: `CAT_MEDICATED_ITEMS` + optional temperature label if provided
- Pet Food: `CAT_PET_FOOD` + optional temperature label if provided
- Printing-Postage: `CAT_POSTAGE` + optional temperature label if provided
- Nontaxable/Tax Exempt: `CAT_NON_TAXABLE` + optional temperature label if provided
- Gift Cards: `CAT_GIFT_CARDS` + optional temperature label if provided
- Storm Preparedness Items: `CAT_STORM_PREP_ITEMS` + optional temperature label if provided
- TPP / Goods: `CAT_TPP` + optional temperature label if provided
- Milk Substitutes: `CAT_MILK_SUBS` + optional temperature label if provided
- Beer: `CAT_BEER` + optional temperature label if provided
- Wine: `CAT_WINE` + optional temperature label if provided
- Liquor: `CAT_LIQUOR` + optional temperature label if provided
- Non Alcoholic Beer or Mocktails: `CAT_NON_ALCOHOLIC_BEER` + optional temperature label if provided
- Juice (Non Carbonated/Under 100%): `CAT_JUICE_NON_CARBONATED` + optional temperature label if provided
- Newspaper: `CAT_NEWSPAPERS` + optional temperature label if provided
- Magazines: `CAT_MAGAZINES` + optional temperature label if provided
- Toilet Tissue: `CAT_TOILET_PAPER` + optional temperature label if provided
- Disposable Paper Products: `CAT_PAPER_PRODUCTS` + optional temperature label if provided
- Vitamins and Supplements: `CAT_SUPPLEMENTS` + optional temperature label if provided
- Contact Solution: `CAT_CONTACT_LENS_SOLUTION` + optional temperature label if provided
- Feminine Hygiene Products: `CAT_FEMININE_HYGIENE_PRODUCTS` + optional temperature label if provided
- Hand Sanitizer: `CAT_HAND_SANITIZER` + optional temperature label if provided
- Lip Balm: `CAT_LIP_BALM` + optional temperature label if provided
- Sunscreen: `CAT_SUNSCREEN` + optional temperature label if provided
- Toothpaste: `CAT_TOOTHPASTE` + optional temperature label if provided
- Toothbrush: `CAT_TOOTHBRUSH` + optional temperature label if provided
- Insecticides, Herbicides, Fungicides: `CAT_INSECTICIDES` + optional temperature label if provided
- Fertilizer: `CAT_FERTILIZER` + optional temperature label if provided
- Vegetable/Fruit Plants: `CAT_FRUIT_VEG_PLANTS` + optional temperature label if provided
- Firewood: `CAT_FIREWOOD` + optional temperature label if provided
- Lighter Fluid: `CAT_LIGHTER_FLUID` + optional temperature label if provided
- Charcoal Briquettes: `CAT_CHARCOAL_BRIQUETTES` + optional temperature label if provided
- Propane: `CAT_PROPANE` + optional temperature label if provided
- Seeds (Human Consumption): `CAT_SEEDS` + optional temperature label if provided
- Bakery Item (Grocery Stores): `CAT_BAKERY_ITEM_GROCERY_STORE` + optional temperature label if provided
- Candy-coated nuts: `CAT_CANDY_COATED_NUTS` + optional temperature label if provided
- Chewing Gum: `CAT_GUM` + optional temperature label if provided
- Chocolate or Chocolate Substitute Candy: `CAT_CHOCOLATE` + optional temperature label if provided
- Confectionary products: `CAT_CONFECTIONARY` + optional temperature label if provided
- 50-25% Juice: `CAT_JUICE_NON_CARBONATED_50TO25` + optional temperature label if provided
- 99-51% Juice: `CAT_JUICE_NON_CARBONATED_99TO51` + optional temperature label if provided
- Cider or perry: `CAT_CIDER` + optional temperature label if provided
- Fortified wine: `CAT_FORTIFIED_WINE` + optional temperature label if provided
- Ice for Consumption - More Than 10 lbs: `CAT_ICE_MORE_THAN_10LBS` + optional temperature label if provided
- Meal Replacement: `CAT_MEAL_REPLACEMENT` + optional temperature label if provided
- Nutritional Shakes: `CAT_NUTRITIONAL_SHAKES` + optional temperature label if provided
- Sparkling wine: `CAT_SPARKLING_WINE` + optional temperature label if provided
- Butter: `CAT_PREPACKAGED_FOOD_BUTTER` + optional temperature label if provided
- Cakes and pies and pastries: `CAT_PREPACKAGED_FOOD_CAKES` + optional temperature label if provided
- Canned or jarred beans: `CAT_PREPACKAGED_FOOD_CANNED_BEANS` + optional temperature label if provided
- Canned or jarred fruit: `CAT_PREPACKAGED_FOOD_CANNED_FRUIT` + optional temperature label if provided
- Canned or jarred vegetables: `CAT_PREPACKAGED_FOOD_CANNED_VEGETABLES` + optional temperature label if provided
- Cereals: `CAT_PREPACKAGED_FOOD_CEREALS` + optional temperature label if provided
- Cheese: `CAT_PREPACKAGED_FOOD_CHEESE` + optional temperature label if provided
- Crackers: `CAT_PREPACKAGED_FOOD_CRACKERS` + optional temperature label if provided
- Desserts and dessert toppings: `CAT_PREPACKAGED_FOOD_DESSERTS` + optional temperature label if provided
- Edible ice cream cups or cones: `CAT_PREPACKAGED_FOOD_ICE_CREAM_CONE` + optional temperature label if provided
- Edible oils and fats: `CAT_PREPACKAGED_FOOD_EDIBLE_OILS` + optional temperature label if provided
- Eggs and egg substitutes: `CAT_PREPACKAGED_FOOD_EGGS` + optional temperature label if provided
- Fresh bread: `CAT_PREPACKAGED_FOOD_FRESH_BREAD` + optional temperature label if provided
- Health or Breakfast Bars: `CAT_PREPACKAGED_FOOD_SNACK_HEALTH_BARS` + optional temperature label if provided
- Honey: `CAT_PREPACKAGED_FOOD_HONEY` + optional temperature label if provided
- Infant Foods: `CAT_PREPACKAGED_FOOD_INFANT_FOOD` + optional temperature label if provided
- Instant Coffee: `CAT_PREPACKAGED_FOOD_INSTANT_COFFEE` + optional temperature label if provided
- Jams or jellies or fruit preserves: `CAT_PREPACKAGED_FOOD_JAMS` + optional temperature label if provided
- Nut or mixed spreads: `CAT_PREPACKAGED_FOOD_NUT_SPREADS` + optional temperature label if provided
- Pickles and relish and olives: `CAT_PREPACKAGED_FOOD_PICKLES` + optional temperature label if provided
- Plain pasta and noodles: `CAT_PREPACKAGED_FOOD_PASTA` + optional temperature label if provided
- Popcorn - Plain: `CAT_PREPACKAGED_FOOD_POPCORN` + optional temperature label if provided
- Salt preserved seafoods: `CAT_PREPACKAGED_FOOD_SALT_PRESERVED_SEA_FOOD` + optional temperature label if provided
- Sauces and spreads and condiments: `CAT_PREPACKAGED_FOOD_CONDIMENTS` + optional temperature label if provided
- Seasonings and preservatives: `CAT_PREPACKAGED_FOOD_SEASONING` + optional temperature label if provided
- Shelf stable milk: `CAT_PREPACKAGED_FOOD_SHELF_STABLE_MILK` + optional temperature label if provided
- Shelf Stable Prepared Potatoes or Rice or Pasta or Stuffing: `CAT_PREPACKAGED_FOOD_SHELF_STABLE_POTATOES` + optional temperature label if provided
- Shelf stable prepared soups or stews: `CAT_PREPACKAGED_FOOD_SHELF_STABLE_SOUP` + optional temperature label if provided
- Tomato purees: `CAT_PREPACKAGED_FOOD_TOMATO_PUREE` + optional temperature label if provided
- Crisps or Chips or Pretzels or Mixes: `CAT_PREPACKAGED_FOOD_SNACK_CHIPS` + optional temperature label if provided
- Nuts or Dried Fruits: `CAT_PREPACKAGED_FOOD_SNACK_NUTS` + optional temperature label if provided
- Snack Bars: `CAT_PREPACKAGED_FOOD_SNACK_SNACK_BARS` + optional temperature label if provided
- Sweet Biscuits or Cookies: `CAT_PREPACKAGED_FOOD_SNACK_COOKIES` + optional temperature label if provided
- Prepared salads: `CAT_PREPARED_FOOD_PREPARED_SALADS` + optional temperature label if provided
- Prepared Side Dishes: `CAT_PREPARED_FOOD_PREPARED_SIDE_DISHES` + optional temperature label if provided
- Fresh fruits: `CAT_PREPACKAGED_FOOD_FRESH_FRUITS` + optional temperature label if provided
- Facial Tissues: `CAT_PAPER_PRODUCTS_FACIAL_TISSUES` + optional temperature label if provided
- Paper napkins or serviettes: `CAT_PAPER_PRODUCTS_PAPER_NAPKINS` + optional temperature label if provided
- Paper Towels: `CAT_PAPER_PRODUCTS_PAPER_TOWELS` + optional temperature label if provided
- Markers: `CAT_SCHOOL_SUPPLIES_MARKERS` + optional temperature label if provided
- Paper Pads or Notebooks: `CAT_SCHOOL_SUPPLIES_NOTEBOOKS` + optional temperature label if provided
- Pencils: `CAT_SCHOOL_SUPPLIES_PENCILS` + optional temperature label if provided
- Pens: `CAT_SCHOOL_SUPPLIES_PENS` + optional temperature label if provided
- Antacids and antiflatulents: `CAT_OTC_MEDICATION_ANTACIDS` + optional temperature label if provided
- Antidiarrheals: `CAT_OTC_MEDICATION_ANTIDIARRHEALS` + optional temperature label if provided
- Antihistamines or H1 blockers: `CAT_OTC_MEDICATION_ANTIHISTAMINES` + optional temperature label if provided
- Combination cold remedies: `CAT_OTC_MEDICATION_COLD_REMEDIES` + optional temperature label if provided
- Decongestants, expectorants, and mucolytics: `CAT_OTC_MEDICATION_DECONGESTANTS` + optional temperature label if provided
- Estrogens and progestins and internal contraceptives: `CAT_OTC_MEDICATION_ESTROGENS` + optional temperature label if provided
- Ibuprofen: `CAT_OTC_MEDICATION_IBUPROFEN` + optional temperature label if provided
- Laxatives: `CAT_OTC_MEDICATION_LAXATIVES` + optional temperature label if provided
- Nasal Decongestants: `CAT_OTC_MEDICATION_NASAL_DECONGESTANTS` + optional temperature label if provided
- Nutritional supplements: `CAT_NUTRITION_SUPPLEMENT` + optional temperature label if provided
- Stimulants and Anorexiants: `CAT_OTC_MEDICATION_STIMULANTS` + optional temperature label if provided
- Air Freshener: `CAT_TPP_AIR_FRESHENER` + optional temperature label if provided
- Antifreeze: `CAT_ANTI_FREEZE` + optional temperature label if provided
- Astringents: `CAT_OTC_MEDICATION_ASTRINGENTS` + optional temperature label if provided
- Bath Gels: `CAT_TPP_BATH_GELS` + optional temperature label if provided
- Bleaches: `CAT_TPP_BLEACHES` + optional temperature label if provided
- Brake oil: `CAT_OIL` + optional temperature label if provided
- Camping and outdoor equipment: `CAT_TPP_CAMPING_EQUIPMENT` + optional temperature label if provided
- Candle: `CAT_TPP_CANDLE` + optional temperature label if provided
- Cigarette lighters or flints: `CAT_TPP_CIGARETTE_LIGHTERS` + optional temperature label if provided
- Cleaning Equipment and Supplies: `CAT_TPP_CLEANING_EQUIPMENT` + optional temperature label if provided
- Cosmetics: `CAT_TPP_COSMETICS` + optional temperature label if provided
- Dental Floss: `CAT_TPP_DENTAL_FLOSS` + optional temperature label if provided
- Deodorants: `CAT_TPP_DEODORANTS` + optional temperature label if provided
- Dishwashing Products: `CAT_TPP_DISH_WASHING_PRODUCTS` + optional temperature label if provided
- Disposable Personal Wipes: `CAT_PAPER_PRODUCTS_PERSONAL_WIPES` + optional temperature label if provided
- Disposable drinking straws: `CAT_PAPER_PRODUCTS_DISPOSABLE_STRAWS` + optional temperature label if provided
- Disposable Kitchenware: `CAT_PAPER_PRODUCTS_DISPOSABLE_KITCHENWARE` + optional temperature label if provided
- Drinkware: `CAT_TPP_DRINK_WARE` + optional temperature label if provided
- Food Storage Containers: `CAT_TPP_CONTAINERS` + optional temperature label if provided
- Kitchen Tools and Utensils: `CAT_TPP_UTENSILS` + optional temperature label if provided
- Drain cleaner: `CAT_TPP_DRAIN_CLEANER` + optional temperature label if provided
- Engine Oil: `CAT_ENGINE_OIL` + optional temperature label if provided
- Flashlight: `CAT_FLASHLIGHT` + optional temperature label if provided
- Gloves, Mittens: `CAT_GLOVES` + optional temperature label if provided
- Hair Combs or Brushes: `CAT_TPP_COMBS` + optional temperature label if provided
- Hand or Body Lotion or Oil: `CAT_TPP_BODY_LOTION` + optional temperature label if provided
- Hand Tools: `CAT_TPP_HAND_TOOLS` + optional temperature label if provided
- Headphones: `CAT_TPP_HEADPHONES` + optional temperature label if provided
- Insect Repellant: `CAT_TPP_INSECT_REPELLENT` + optional temperature label if provided
- Laundry Products: `CAT_TPP_LAUNDRY_PRODUCTS` + optional temperature label if provided
- Masks or accessories: `CAT_TPP_MASKS` + optional temperature label if provided
- Medical Thermometers and Accessories: `CAT_THERMOMETERS` + optional temperature label if provided
- Mouthwash: `CAT_TPP_MOUTH_WASH` + optional temperature label if provided
- Nail Clippers: `CAT_TPP_NAIL_CLIPPERS` + optional temperature label if provided
- Nail Polish Remover: `CAT_TPP_NAIL_POLISH_REMOVER` + optional temperature label if provided
- Perfumes or Colognes or Fragrances: `CAT_TPP_PERFUMES` + optional temperature label if provided
- Playing Cards: `CAT_TPP_PLAYING_CARDS` + optional temperature label if provided
- Razors: `CAT_TPP_RAZORS` + optional temperature label if provided
- Scouring pads: `CAT_TPP_SCOURING_PADS` + optional temperature label if provided
- Shampoos: `CAT_TPP_SHAMPOOS` + optional temperature label if provided
- Shaving Creams: `CAT_TPP_SHAVING_CREAMS` + optional temperature label if provided
- Skin Care Products: `CAT_TPP_SKIN_CARE_PRODUCTS` + optional temperature label if provided
- Sponges: `CAT_TPP_SPONGES` + optional temperature label if provided
- Standard envelopes: `CAT_TPP_ENVELOPES` + optional temperature label if provided
- Tape: `CAT_TPP_TAPE` + optional temperature label if provided
- Toys and Games: `CAT_TPP_TOYS` + optional temperature label if provided
- Trash bags: `CAT_TPP_TRASH_BAGS` + optional temperature label if provided
- Umbrellas: `CAT_TPP_UMBRELLAS` + optional temperature label if provided
- Ice Cream (Larger than Pint): `CAT_ICE_CREAM_PINTS` + optional temperature label if provided
- Combo Meals or Gift Baskets: `CAT_COMBOS_BUNDLES` + optional temperature label if provided
- Cannabis: `CAT_CANNABIS` + optional temperature label if provided
- Books: `CAT_BOOK` + optional temperature label if provided
- Child/Baby Car Seats: `CAT_CHILD_CAR_SEAT` + optional temperature label if provided
- Child/Baby Clothing: `CAT_CHILD_CLOTHING` + optional temperature label if provided
- Sustainable Packaging: `CAT_SUSTAINABLE_PACKAGING` + optional temperature label if provided