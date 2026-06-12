# A Laravel client to integrate with the Uber Eats API
This package allows you to easily make requests to the new Uber Eats Marketplace API.

## Requirements

- PHP >= 8.3
- Laravel >= 12 or >= 13

## Installation

You can install the package via composer:

```bash
composer require morscate/uber-eats
```

The package will automatically register itself.

## Configuration
To start using the Uber Eats API you will need a client ID and client secret. You can get these by creating an app on the [Uber Developer Portal](https://developer.uber.com/dashboard/).
Add the Client ID and client secret to your .env file:
```
UBER_EATS_CLIENT_ID=
UBER_EATS_CLIENT_SECRET=
UBER_EATS_SCOPE=
```

## Making requests

### Create your own request
If you need to make a request not covered by this package, you can use the underlying HTTP client directly:
```php
$uberEatsApi = new UberEatsApi();
$uberEatsApi->request('https://api.uber.com/v1/delivery')->get('/store/{storeId}/orders');
```

---

## Integrations

### Activate an integration
Before you can start using the API, you need to activate a store integration. Make sure you have access to the `eats.pos_provisioning` scope. When you have access, add it to `UBER_EATS_SCOPE` in your `.env`.

```php
$uberEatsApi = new UberEatsApi();
$uberEatsApi->activateIntegration(
    storeId: '{store_id}',
    isOrderManager: true,
    integratorStoreId: '{integrator_store_id}',
    integratorBrandId: '{integrator_brand_id}',
);
```

### Get integration details
```php
$uberEatsApi = new UberEatsApi();
$uberEatsApi->getIntegrationDetails(storeId: '{store_id}');
```

### Update an integration
```php
$uberEatsApi = new UberEatsApi();
$uberEatsApi->updateIntegration(
    storeId: '{store_id}',
    integrationEnabled: true,
    isOrderManager: true,
    integratorStoreId: '{integrator_store_id}',
    integratorBrandId: '{integrator_brand_id}',
);
```

---

## Orders

### Get all orders for a store
```php
$uberEatsApi = new UberEatsApi();
$uberEatsApi->getOrders(storeId: '{store_id}');
```

### Get a single order
```php
$uberEatsApi = new UberEatsApi();
$uberEatsApi->getOrder(orderId: '{order_id}');
```

### Accept an order
```php
use Carbon\Carbon;

$uberEatsApi = new UberEatsApi();
$uberEatsApi->acceptOrder(
    orderId: '{order_id}',
    pickupAt: Carbon::now()->addMinutes(20), // optional
    externalId: '{your_internal_order_id}',  // optional
    acceptedBy: '{employee_name}',           // optional
);
```

### Deny an order
```php
use Morscate\UberEats\Enums\ReasonType;

$uberEatsApi = new UberEatsApi();
$uberEatsApi->denyOrder(
    orderId: '{order_id}',
    reasonInfo: 'Kitchen is closed for the evening.',
    reasonType: ReasonType::KITCHEN_CLOSED,
);
```

### Cancel an order
```php
use Morscate\UberEats\Enums\ReasonType;

$uberEatsApi = new UberEatsApi();
$uberEatsApi->cancelOrder(
    orderId: '{order_id}',
    reasonInfo: 'Item is no longer available.',
    reasonType: ReasonType::ITEM_ISSUE,
);
```

### Update the ready time for an order
```php
use Carbon\Carbon;

$uberEatsApi = new UberEatsApi();
$uberEatsApi->updateOrderReadyTime(
    orderId: '{order_id}',
    readyForPickupTime: Carbon::now()->addMinutes(30),
);
```

### Mark an order as ready
```php
$uberEatsApi = new UberEatsApi();
$uberEatsApi->markOrderReady(orderId: '{order_id}');
```

#### `ReasonType` enum values

| Value | Description |
|---|---|
| `ITEM_ISSUE` | Issue with an item or modifier |
| `KITCHEN_CLOSED` | Kitchen is closed |
| `CUSTOMER_CALLED_TO_CANCEL` | Customer called to cancel |
| `RESTAURANT_TOO_BUSY` | Restaurant is too busy |
| `ORDER_VALIDATION` | Order validation error |
| `STORE_CLOSED` | Store is closed |
| `TECHNICAL_FAILURE` | Technical failure |
| `POS_NOT_READY` | POS not ready |
| `POS_OFFLINE` | POS is offline |
| `CAPACITY` | Store order capacity is full |
| `ADDRESS` | Problem with address |
| `SPECIAL_INSTRUCTIONS` | Special instructions issue |
| `PRICING` | Pricing issues |
| `UNKNOWN` | Unknown reason |
| `OTHER` | Other |

---

## Menus

### Get the menu for a store
```php
$uberEatsApi = new UberEatsApi();
$uberEatsApi->getMenu(storeId: '{store_id}');
```

### Create or update a menu
Sends a full menu payload to Uber Eats. See the [Uber Eats menu API docs](https://developer.uber.com/docs/eats/references/api/v2/put-eats-stores-storeid-menu) for the expected menu structure.

```php
$uberEatsApi = new UberEatsApi();
$uberEatsApi->upsertMenu(
    storeId: '{store_id}',
    menu: [
        // full menu payload
    ],
);
```

---

## Couriers (BYOC)

### Ingest courier live location
Used when using Bring Your Own Courier (BYOC) to report the courier's current GPS position to Uber Eats.

```php
$uberEatsApi = new UberEatsApi();
$uberEatsApi->ingestCourierLiveLocation(
    orderId: '{order_workflow_uuid}',
    restaurantId: '{restaurant_uuid}',
    latitude: '52.3702',
    longitude: '4.8952',
    updatedAt: null, // optional epoch milliseconds, defaults to now
);
```

---

## Webhooks
To start receiving webhooks from Uber Eats, add the following route in `App\Providers\RouteServiceProvider`:
```php
$this->routes(function () {
    // ...
    Route::uberEatsWebhooks();
});
```

## Security Vulnerabilities

If you discover a security vulnerability within this project, please email me via [development@morscate.nl](mailto:development@morscate.nl).
