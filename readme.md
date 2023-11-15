# Implement the new Uber Eats Marketplace API
This package allows you to easily make requests to the new Uber Eats Marketplace API.

## Requirements

- PHP >= 8.1
- Laravel >= 9.0

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

## Making requests
### Activate an integration
Before you can start using the API, you need to activate an store integration. To do so you need to make sure to have access to the `eats.pos_provisioning` scope (https://developer.uber.com/docs/eats/references/api/v1/post-eats-stores-storeid-posdata). When you have access to this scope add it to the `UBER_EATS_SCOPE` through the config or .env.

You can do this by using the following code:
```php
$uberEatsApi = new UberEatsApi();
$uberEatsApi->activateIntegration(
        storeId: "{store_id}",
        isOrderManager: true
    );
```

### Getting orders
To get all orders from a store, you can use the following code:
```php
$uberEatsApi = new UberEatsApi();
$uberEatsApi->getOrders("{store_id}");
```

### Create your own request
If you need to create your own request, you can use the following code:
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

## Security Vulnerabilities

If you discover a security vulnerability within this project, please email me via [development@morscate.nl](mailto:development@morscate.nl).
