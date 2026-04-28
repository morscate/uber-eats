<?php

declare(strict_types=1);

namespace Morscate\UberEats\Endpoints;

trait ManagesStores
{
    /**
     * Get all stores linked to this token
     * 
     * @param string|null $nextPageToken Token for requesting a specific page
     * @param int $pageSize Maximum number of stores per page (default: 50)
     * @return object
     */
    public function getStores(?string $nextPageToken = null, int $pageSize = 50)
    {
        $query = ['page_size' => $pageSize];
        
        if ($nextPageToken) {
            $query['next_page_token'] = $nextPageToken;
        }
        
        $queryString = http_build_query($query);
        
        $response = $this->request($this->apiUrl('/v1/delivery'))
            ->get("/stores?{$queryString}");

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }

    /**
     * Get detailed information about a specific store
     * 
     * @param string $storeId Store ID to retrieve
     * @param array|null $expand Fields to expand (e.g., ['holiday_hours', 'internal_contact_emails'])
     * @return object
     */
    public function getStoreDetails(string $storeId, ?array $expand = null)
    {
        $url = "/store/{$storeId}";
        
        if ($expand && count($expand) > 0) {
            $expandParam = implode(',', $expand);
            $url .= "?expand={$expandParam}";
        }
        
        $response = $this->request($this->apiUrl('/v1/delivery'))
            ->get($url);

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }

    /**
     * Update store information (contact, location, pickup instructions)
     * 
     * @param string $storeId Store ID to update
     * @param array $data Store data to update (contact, location, pickup_instructions)
     * @return object
     */
    public function updateStoreInfo(string $storeId, array $data)
    {
        $response = $this->request($this->apiUrl('/v1/delivery'))
            ->post("/store/{$storeId}", $data);

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }

    /**
     * Get the current orderability status of a store
     * 
     * @param string $storeId Store ID
     * @return object Returns status, is_offline_until, offline_reason, etc.
     */
    public function getStoreStatus(string $storeId)
    {
        $response = $this->request($this->apiUrl('/v1/delivery'))
            ->get("/store/{$storeId}/status");

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }

    /**
     * Set store online/offline status
     * 
     * @param string $storeId Store ID
     * @param string $status "ONLINE" or "OFFLINE"
     * @param string|null $reason Reason for status change
     * @param string|null $isOfflineUntil RFC3339 timestamp until when store is offline
     * @return object
     */
    public function setStoreStatus(
        string $storeId,
        string $status,
        ?string $reason = null,
        ?string $isOfflineUntil = null
    ) {
        $data = ['status' => $status];
        
        if ($reason) {
            $data['reason'] = $reason;
        }
        
        if ($isOfflineUntil) {
            $data['is_offline_until'] = $isOfflineUntil;
        }
        
        $response = $this->request($this->apiUrl('/v1/delivery'))
            ->post("/store/{$storeId}/update-store-status", $data);

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }

    /**
     * Pause store (set offline)
     * 
     * @param string $storeId Store ID
     * @param string $reason Reason for pausing
     * @param string|null $until RFC3339 timestamp until when to pause
     * @return object
     */
    public function pauseStore(string $storeId, string $reason = 'Temporarily closed', ?string $until = null)
    {
        return $this->setStoreStatus($storeId, 'OFFLINE', $reason, $until);
    }

    /**
     * Unpause store (set online)
     * 
     * @param string $storeId Store ID
     * @return object
     */
    public function unpauseStore(string $storeId)
    {
        return $this->setStoreStatus($storeId, 'ONLINE');
    }

    /**
     * Update store preparation time
     * 
     * @param string $storeId Store ID
     * @param int $defaultPrepTimeSeconds Prep time in seconds (max: 10,800 = 3 hours)
     * @return object
     */
    public function updateStorePrepTime(string $storeId, int $defaultPrepTimeSeconds)
    {
        $data = ['default_prep_time' => $defaultPrepTimeSeconds];
        
        $response = $this->request($this->apiUrl('/v1/delivery'))
            ->post("/store/{$storeId}/update-store-prep-time", $data);

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }

    /**
     * Update fulfillment configuration for BYOC (Bring Your Own Courier) stores
     * 
     * @param string $storeId Store ID
     * @param array $overrideConfig Configuration to override (e.g., ['custom_min_etd_minutes' => 30])
     * @return object
     */
    public function updateFulfillmentConfiguration(string $storeId, array $overrideConfig)
    {
        $data = ['override_config' => $overrideConfig];
        
        $response = $this->request($this->apiUrl('/v1/delivery'))
            ->post("/store/{$storeId}/update-fulfillment-configuration", $data);

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }
}
