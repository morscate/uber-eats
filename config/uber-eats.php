<?php

return [
    'client_id' => env('SANDBOX_UBER_EATS_CLIENT_ID', env('UBER_EATS_CLIENT_ID')),

    'client_secret' => env('SANDBOX_UBER_EATS_CLIENT_SECRET', env('UBER_EATS_CLIENT_SECRET')),

    // Required scopes for full functionality:
    // - eats.store: Read store information
    // - eats.store.status.write: Update store status (online/offline)
    // - eats.order: Manage orders
    // - eats.store.orders.read: Read orders
    // - store:write: Update store details
    // - eats.byoc.fulfillment.config: Update BYOC fulfillment configuration
    'scope' => env('UBER_EATS_SCOPE', 'eats.store eats.store.status.write eats.order eats.store.orders.read'),

    'api_base_url' => env('SANDBOX_UBER_EATS_API_BASE_URL', env('UBER_EATS_API_BASE_URL', 'https://test-api.uber.com')),

    'auth_url' => env('SANDBOX_UBER_EATS_AUTH_URL', env('UBER_EATS_AUTH_URL', 'https://sandbox-login.uber.com/oauth/v2/token')),
];
