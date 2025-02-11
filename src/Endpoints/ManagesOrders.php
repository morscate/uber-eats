<?php

declare(strict_types=1);

namespace Morscate\UberEats\Endpoints;

use Carbon\Carbon;
use Morscate\UberEats\Enums\ReasonType;

trait ManagesOrders
{
    protected string $orderUrl = 'https://api.uber.com/v1/delivery';

    public function getOrders(string $storeId)
    {
        $response = $this->request($this->orderUrl)
            ->get("/store/{$storeId}/orders?expand=deliveries,carts,payment");

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }

    public function getOrder(string $orderId)
    {
        $response = $this->request($this->orderUrl)
            ->get("/order/{$orderId}?expand=deliveries,carts,payment");

        if ($response->successful()) {
            return $response->object();
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

        $response = $this->request($this->orderUrl)
            ->post(
                "/order/{$orderId}/accept",
                $data
            );

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }

    public function denyOrder(
        string $orderId,
        string $reasonInfo = null,
        ReasonType $reasonType = null,
    ) {
        $response = $this->request($this->orderUrl)
            ->post(
                "/order/{$orderId}/deny",
                $this->transformReason($reasonInfo, $reasonType->value)
            );

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }

    public function cancelOrder(
        string $orderId,
        string $reasonInfo = null,
        ReasonType $reasonType = null,
    ) {
        $response = $this->request($this->orderUrl)->post(
            "/order/{$orderId}/cancel",
            $this->transformReason($reasonInfo, $reasonType->value)
        );

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }

    private function transformReason(
        string $info = null,
        string $type = null,
        string $clientErrorCode = null,
        array $itemMetadata = null,
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

        if ($itemMetadata) {
            $reason['item_metadata'] = $itemMetadata;
        }

        return $reason;
    }

    public function updateOrderReadyTime(
        string $orderId,
        Carbon $readyForPickupTime
    ) {
        $response = $this->request($this->orderUrl)
            ->asJson()
            ->post(
                "/order/{$orderId}/update-ready-time",
                (object) [
                    'ready_for_pickup_time' => $readyForPickupTime->toRfc3339String(),
                ]
            );

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }

    public function markOrderReady(string $orderId)
    {
        $response = $this->request($this->orderUrl)
            ->post("/order/{$orderId}/ready", (object) []);

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }
}
