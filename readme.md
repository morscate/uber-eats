# A Laravel client to integrate with the Uber Eats API
This package allows you to easily make requests to the new Uber Eats Marketplace API.

## Requirements

- PHP >= 8.3
- Laravel 11.x / 12.x

## Installation

You can install the package via composer:

```bash
composer require morscate/uber-eats
```

The package will automatically register itself.

## Configuration
To start using the Uber Eats API you will need a client ID and client secret. You can get these by creating an app on the [Uber Developer Portal](https://developer.uber.com/dashboard/).
Add the Client ID and client secret to your .env file:
```php
UBER_EATS_CLIENT_ID=
UBER_EATS_CLIENT_SECRET=
```

### Testing vs Production domains

Use matching API and authentication domains for each application type.

Testing Applications:

- API Calls: `https://test-api.uber.com`
- Authentication: `https://sandbox-login.uber.com/oauth/v2/token`
- Scopes: Automatically granted for sandbox domains only

Production Applications:

- API Calls: `https://api.uber.com`
- Authentication: `https://auth.uber.com/oauth/v2/token`
- Scopes: Manual approval required

Example `.env` for Testing:

```php
UBER_EATS_API_BASE_URL=https://test-api.uber.com
UBER_EATS_AUTH_URL=https://sandbox-login.uber.com/oauth/v2/token
```

Example `.env` for Production:

```php
UBER_EATS_API_BASE_URL=https://api.uber.com
UBER_EATS_AUTH_URL=https://auth.uber.com/oauth/v2/token
```

## Available Endpoints

### Store Management

**Get All Stores**
```php
$stores = $api->getStores(
    nextPageToken: null,  // Optional - for pagination
    pageSize: 50          // Optional - default is 50
);
// Returns list of stores with pagination data
```

**Get Store Details**
```php
// Basic details
$store = $api->getStoreDetails("{store_id}");

// With expanded fields
$store = $api->getStoreDetails("{store_id}", ['holiday_hours', 'internal_contact_emails']);
```

**Update Store Information**
```php
$api->updateStoreInfo("{store_id}", [
    'contact' => [
        'email' => 'manager@restaurant.com',
        'name' => 'Jane Doe',
        'phone_number' => '+1-800-999-9999'
    ],
    'location' => [
        'street_address_line_one' => '175 Greenwich St',
        'city' => 'New York',
        'postal_code' => '10007',
        // ... other location fields
    ],
    'pickup_instructions' => 'Enter from north entrance'
]);
```

**Get Store Status**
```php
$status = $api->getStoreStatus("{store_id}");
// Returns: status, is_offline_until, offline_reason, etc.
```

**Set Store Online/Offline**
```php
// Set offline
$api->setStoreStatus(
    storeId: "{store_id}",
    status: "OFFLINE",
    reason: "Scheduled maintenance",
    isOfflineUntil: "2026-04-28T18:00:00+00:00"  // RFC3339 format
);

// Set online
$api->setStoreStatus("{store_id}", "ONLINE");
```

**Pause/Unpause Store (Helper Methods)**
```php
// Pause store
$api->pauseStore(
    storeId: "{store_id}",
    reason: "Kitchen closed for cleaning",
    until: "2026-04-28T15:00:00+00:00"  // Optional
);

// Unpause store
$api->unpauseStore("{store_id}");
```

**Update Store Prep Time**
```php
// Set prep time to 15 minutes (900 seconds)
$api->updateStorePrepTime("{store_id}", 900);

// Max: 10,800 seconds (3 hours)
```

**Update Fulfillment Configuration (BYOC)**
```php
// For Bring Your Own Courier stores
$api->updateFulfillmentConfiguration("{store_id}", [
    'custom_min_etd_minutes' => 30
]);
```

### Store Integration (POS Data)

**Activate Integration**
```php
$api = new UberEatsApi();
$api->activateIntegration(
    storeId: "{store_id}",
    isOrderManager: true,
    integratorStoreId: "your_pos_store_id",
    integratorBrandId: "your_brand_id"
);
```

**Get Integration Details**
```php
$details = $api->getIntegrationDetails("{store_id}");
```

**Update Integration**
```php
$api->updateIntegration(
    storeId: "{store_id}",
    integrationEnabled: true,
    isOrderManager: true,
    integratorStoreId: "your_pos_store_id",
    integratorBrandId: "your_brand_id"
);
```

**Configure Webhook** (Note: Usually configured in Developer Portal)
```php
$api->configureWebhook(
    storeId: "{store_id}",
    webhookUrl: "https://your-domain.com/webhooks/uber-eats"
);
```

### Store Menu Management

**Get Menu**
```php
$menu = $api->getMenu("{store_id}");
// Returns menu with categories, items, and modifier groups
```

**Upload/Update Menu**
```php
$menuData = [
    'menus' => [...],
    'categories' => [...],
    'items' => [...],
    'modifier_groups' => [...]
];

$success = $api->upsertMenu("{store_id}", $menuData);
```

See `dummy-menu.full.json` for complete menu structure example.

### Order Management

**Get Store Orders**
```php
$orders = $api->getOrders("{store_id}");
// Returns all orders for the store with delivery, cart, and payment details
```

**Get Specific Order**
```php
$order = $api->getOrder("{order_id}");
// Returns full order details
```

**Accept Order**
```php
use Carbon\Carbon;

$api->acceptOrder(
    orderId: "{order_id}",
    pickupAt: Carbon::now()->addMinutes(20),  // Optional
    externalId: "POS-ORDER-123",              // Optional - your POS order ID
    acceptedBy: "Chef John"                   // Optional
);
```

**Deny Order**
```php
use Morscate\UberEats\Enums\ReasonType;

$api->denyOrder(
    orderId: "{order_id}",
    reasonInfo: "Kitchen is closed",
    reasonType: ReasonType::KITCHEN_CLOSED
);
```

**Cancel Order**
```php
$api->cancelOrder(
    orderId: "{order_id}",
    reasonInfo: "Out of ingredients",
    reasonType: ReasonType::ITEM_ISSUE
);
```

**Update Order Ready Time**
```php
$api->updateOrderReadyTime(
    orderId: "{order_id}",
    readyForPickupTime: Carbon::now()->addMinutes(15)
);
```

**Mark Order Ready for Pickup**
```php
$api->markOrderReady("{order_id}");
```

### Courier Management (BYOC - Bring Your Own Courier)

**Send Courier Live Location**
```php
$api->ingestCourierLiveLocation(
    orderId: "{order_id}",
    restaurantId: "{store_id}",
    latitude: "40.7128",
    longitude: "-74.0060",
    updatedAt: time() * 1000  // Optional - milliseconds
);
```

## Available Enums

The package includes typed enums for API constants:

- `ReasonType` - Order cancellation/denial reasons
- `OrderState` - Order lifecycle states
- `OrderStatus` - Order status types
- `FulfillmentType` - Delivery types (Uber, Merchant, Pickup, Dine-in)
- `PreparationStatus` - Kitchen preparation statuses
- And 13 more...

Example usage:
```php
use Morscate\UberEats\Enums\ReasonType;
use Morscate\UberEats\Enums\FulfillmentType;

// Type-safe reason codes
$api->denyOrder($orderId, "Too busy", ReasonType::RESTAURANT_TOO_BUSY);

// Get enum descriptions
echo ReasonType::RESTAURANT_TOO_BUSY->description();
// Output: "Restaurant is too busy"
```

## Making Custom Requests

If you need to call an endpoint not yet wrapped:
```php
$uberEatsApi = new UberEatsApi();
$uberEatsApi->request()->get('https://api.uber.com/v1/delivery/store/{$storeId}/orders');
```

## Webhooks
To start receiving webhooks from Uber Eats, you need to add the following route the `App\Providers\RouteServiceProvider` file:
```php
$this->routes(function () {
    // ...
    Route::uberEatsWebhooks();
});
```

## Testing & Development

### Sandbox Testing Utilities

The `sandbox/` directory contains helpful tools for testing your integration:

**Order Simulator** (`sandbox/order-simulator.php`)
- Simulates Uber Eats order flow
- Tests webhook delivery
- Provides sample order data

Run with:
```bash
php -S 127.0.0.1:8090 sandbox/order-simulator.php
```

**Webhook Receiver** (`sandbox/webhook-receiver.php`)
- Standalone webhook endpoint for testing
- Logs all incoming webhooks to `sandbox/webhook-events.log.jsonl`

Run with:
```bash
php -S 127.0.0.1:8090 sandbox/webhook-receiver.php
```

### Sample Menu Data

The `dummy-menu.full.json` file contains a complete example menu structure showing:
- Menu hierarchy (menus → categories → items)
- Item pricing and descriptions
- Modifier groups and options
- Multi-language support

Use this as a reference when building your menu upload functionality.

## Security Vulnerabilities

If you discover a security vulnerability within this project, please email me via [development@morscate.nl](mailto:development@morscate.nl).
