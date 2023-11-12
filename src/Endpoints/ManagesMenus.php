<?php

declare(strict_types=1);

namespace Morscate\UberEats\Endpoints;

trait ManagesMenus
{
    protected string $menuUrl = 'https://api.uber.com/v2/eats/stores';

    public function getMenu(string $storeId): ?object
    {
        $response = $this->request($this->menuUrl)
            ->get("/{$storeId}/menus");

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();

        return null;
    }

    /**
     * Send the menu to Uber Eats.
     *
     * @todo gzip https://developer.uber.com/docs/eats/references/api/v2/put-eats-stores-storeid-menu
     */
    public function upsertMenu(string $storeId, array $menu): bool
    {
        $response = $this->request($this->menuUrl)
            ->asJson()
            ->put("/v2/eats/stores/{$storeId}/menus", $menu);

        if ($response->successful()) {
            return true;
        }

        $response->throw();

        return false;
    }
}
