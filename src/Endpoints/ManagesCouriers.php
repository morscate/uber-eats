<?php

declare(strict_types=1);

namespace Morscate\UberEats\Endpoints;

trait ManagesCouriers
{
    protected string $courierUrl = 'https://api.uber.com/v1/eats/stores';

    public function ingestCourierLiveLocation(
        string $orderId,
        string $restaurantId,
        string $latitude,
        string $longitude,
        ?int $updatedAt = null,
    ) {
        $data = [
            'order_workflow_uuid' => $orderId,
            'restaurant_uuid' => $restaurantId,
            'position_event' => [
                'point' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ],
                'time' => [
                    'epochMillis' => $updatedAt ?? floor(microtime(true) * 1000),
                ],
            ],
        ];

        $response = $this->request($this->courierUrl)
            ->post(
                '/v1/eats/byoc/restaurants/orders/event/location',
                $data
            );

        if ($response->successful()) {
            return $response->json();
        }

        $response->throw();
    }
}
