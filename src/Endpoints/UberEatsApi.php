<?php

declare(strict_types=1);

namespace Morscate\UberEats\Endpoints;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class UberEatsApi
{
    private string $tokenUrl = 'https://login.uber.com/oauth/v2/token';

    protected string $baseUrl = 'https://api.uber.com';

    protected function request(): PendingRequest
    {
        $accessToken = $this->getAccessToken();

        return Http::baseUrl($this->baseUrl)
            ->asJson()
            ->withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
            ]);
    }

    /**
     * Get a client access token that we can use to authenticate against the Uber API
     *
     * @see https://developer.uber.com/docs/eats/guides/authentication
     *
     * @todo add eats.byoc.position
     */
    private function getAccessToken(): ?string
    {
        $cacheKey = 'uberEats:accessToken';

        $accessToken = Cache::get($cacheKey);

        if ($accessToken) {
            return $accessToken;
        }

        $response = Http::asForm()
            ->post(
                $this->tokenUrl,
                [
                    'client_id' => config('services.uber_eats.client_id'),
                    'client_secret' => config('services.uber_eats.client_secret'),
                    'scope' => config('services.uber_eats.scope'),
                    'grant_type' => 'client_credentials',
                ]
            );

        if ($response->successful()) {
            $data = $response->json();

            // cache the access token, so we don't have to request a new one every time
            Cache::put(
                $cacheKey,
                $data['access_token'],
                // -30 seconds to prevent possibility of request failing because the token expired last second
                now()->addSeconds($data['expires_in'] - 30)
            );

            return $data['access_token'];
        }

        // log & alert if this request somehow fails, possibly means that our client credentials are outdated
        $response->throw();

        return null;
    }
}
