<?php

declare(strict_types=1);

namespace Morscate\UberEats\Endpoints;

trait ManagesIntegrations
{
    protected string $integrationUrl = 'https://api.uber.com/v1/eats/stores';

    public function activateIntegration(
        string $storeId,
        bool $isOrderManager = null,
        string $integratorStoreId = null,
        string $integratorBrandId = null,
    ) {
        $data = [
            'is_order_manager' => $isOrderManager,
            'integrator_store_id' => $integratorStoreId,
            'integrator_brand_id' => $integratorBrandId,
        ];

        $response = $this->request($this->integrationUrl)
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
        $response = $this->request($this->integrationUrl)
            ->get("/{$storeId}/pos_data");

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }

    public function updateIntegration(
        string $storeId,
        bool $integrationEnabled = null,
        bool $isOrderManager = null,
        string $integratorStoreId = null,
        string $integratorBrandId = null,
    ) {
        $data = [
            'integration_enabled' => $integrationEnabled,
            'is_order_manager' => $isOrderManager,
            'integrator_store_id' => $integratorStoreId,
            'integrator_brand_id' => $integratorBrandId,
        ];

        $response = $this->request($this->integrationUrl)
            ->patch(
                "/{$storeId}/pos_data",
                array_filter($data)
            );

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }
}
