<?php

return [
    'client_id' => env('UBER_EATS_CLIENT_ID'),

    'client_secret' => env('UBER_EATS_CLIENT_SECRET'),

    'scope' => env('UBER_EATS_SCOPE', 'eats.store eats.store.status.write eats.order eats.store.orders.read'),
];
