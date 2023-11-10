<?php

declare(strict_types=1);

namespace Morscate\UberEats\Endpoints;

use Carbon\Carbon;
use Morscate\UberEats\Enums\CancelType;

class UberEatsOrderApi extends UberEatsApi
{
    protected string $baseUrl = 'https://api.uber.com/v1/delivery';

    public function getOrders(string $storeId): array
    {
        $response = $this->request()->get("/store/{$storeId}/orders");

        if ($response->successful()) {
            return $response->object()->data;
        }

        $response->throw();
    }

    public function getOrder(string $orderId)
    {
        $response = $this->request()->get("/order/{$orderId}?expand=deliveries,carts,payment");

        if ($response->successful()) {
            return $response->json();
        }

        $response->throw();
    }

    public function acceptOrder(
        string $orderId,
        Carbon $pickupAt,
        string $externalId = null,
        string $acceptedBy = null,
    ) {
        $data = [
            'ready_for_pickup_time' => $pickupAt->toRfc3339String(),
        ];

        if ($externalId) {
            $data['external_id'] = $externalId;
        }

        if ($acceptedBy) {
            $data['accepted_by'] = $acceptedBy;
        }

        $response = $this->request()->post(
            "/order/{$orderId}/accept",
            $data
        );

        if ($response->successful()) {
            return $response->json();
        }

        $response->throw();
    }

    public function denyOrder(
        string $orderId,
        string $reasonInfo = null,
        string $reasonType = null,
    ) {
        $response = $this->request()->post(
            "/order/{$orderId}/deny",
            $this->transformReason($reasonInfo, $reasonType)
        );

        if ($response->successful()) {
            return $response->json();
        }

        $response->throw();
    }

    public function cancelOrder(
        string $orderId,
        string $reasonInfo = null,
        CancelType $reasonType = null,
    ) {
        dd($reasonType);

        $response = $this->request()->post(
            "/order/{$orderId}/cancel",
            $this->transformReason($reasonInfo, $reasonType->value)
        );

        if ($response->successful()) {
            return $response->json();
        }

        $response->throw();
    }

    private function transformReason(
        string $info = null,
        string $type = null,
        string $clientErrorCode = null
    ): array {
        $reason = [];

        if ($info) {
            $reason['info'] = $info;
        }

        // @todo create enum https://developer.uber.com/docs/eats/references/api/order_suite#tag/CancelOrder/operation/cancelOrder
        if ($type) {
            $reason['type'] = $type;
        }

        if ($clientErrorCode) {
            $reason['client_error_code'] = $clientErrorCode;
        }

        //item_metadata

        return $reason;
    }

    public function updateOrderReadyTime(string $orderId, string $readyForPickupTime)
    {
        $response = $this->request()->asJson()->post(
            "/order/{$orderId}/update-ready-time",
            (object) [
                'ready_for_pickup_time' => $readyForPickupTime,
            ]
        );

        if ($response->successful()) {
            return $response->json();
        }

        $response->throw();
    }

    public function markOrderReady(string $orderId)
    {
        $response = $this->request()
            ->post("/order/{$orderId}/ready", (object) []);

        if ($response->successful()) {
            return $response->json();
        }

        $response->throw();
    }
}
