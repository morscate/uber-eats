<?php

return [
    'uber_eats_client_id' => env('UBER_EATS_CLIENT_ID'),

    'uber_eats_client_secret' => env('UBER_EATS_CLIENT_SECRET'),

    'uber_eats_scope' => env('UBER_EATS_SCOPE', 'eats.store eats.store.status.write eats.order eats.store.orders.read'),
];
