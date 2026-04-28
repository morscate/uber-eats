<?php

declare(strict_types=1);

namespace Morscate\UberEats\Endpoints;

trait ManagesIntegrations
{
    public function activateIntegration(
        string $storeId,
        bool $isOrderManager,
        string $integratorStoreId,
        string $integratorBrandId,
    ) {
        $data = [
            'is_order_manager' => $isOrderManager,
            'integrator_store_id' => $integratorStoreId,
            'integrator_brand_id' => $integratorBrandId,
        ];

        $response = $this->request($this->apiUrl('/v1/eats/stores'))
            ->post(
                "/{$storeId}/pos_data",
                array_filter($data)
            );

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }

    public function getIntegrationDetails(string $storeId)
    {
        $response = $this->request($this->apiUrl('/v1/eats/stores'))
            ->get("/{$storeId}/pos_data");

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }

    public function updateIntegration(
        string $storeId,
        bool $integrationEnabled,
        bool $isOrderManager,
        string $integratorStoreId,
        string $integratorBrandId,
    ) {
        $data = [
            'integration_enabled' => $integrationEnabled,
            'is_order_manager' => $isOrderManager,
            'integrator_store_id' => $integratorStoreId,
            'integrator_brand_id' => $integratorBrandId,
        ];

        $response = $this->request($this->apiUrl('/v1/eats/stores'))
            ->patch(
                "/{$storeId}/pos_data",
                array_filter($data)
            );

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }

    /**
     * Configure webhook URL for a store
     * Note: Webhook URLs are typically configured in the Uber Developer Portal
     * This method attempts to set it via the API if supported
     */
    public function configureWebhook(
        string $storeId,
        string $webhookUrl,
    ) {
        $data = [
            'webhook_url' => $webhookUrl,
        ];

        // Try updating via pos_data endpoint
        $response = $this->request($this->apiUrl('/v1/eats/stores'))
            ->patch(
                "/{$storeId}/pos_data",
                $data
            );

        if ($response->successful()) {
            return $response->object();
        }

        // If that doesn't work, the webhook URL needs to be configured
        // manually in the Uber Developer Portal
        $response->throw();
    }
}
