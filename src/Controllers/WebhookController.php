<?php

namespace Morscate\UberEats\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Morscate\UberEats\UberEatsWebhook;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        if (! $this->hasValidSignature($request)) {
            return response()->json('Invalid signature', 401);
        }

        $webhook = $this->transformNotification($request);

        try {
            Event::dispatch($webhook->eventName(), $webhook);

            return response()->noContent(200, ['Content-Type' => 'application/json']);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json('Error handling webhook', 500);
        }
    }

    private function transformNotification(Request $request): UberEatsWebhook
    {
        $notification = $request->all();

        return UberEatsWebhook::fromNotification($notification);
    }

    private function hasValidSignature(Request $request): bool
    {
        $clientSecret = config('services.uber_eats.client_secret');
        $signature = hash_hmac('sha256', $request->getContent(), $clientSecret);

        return hash_equals($request->header('X-Uber-Signature'), $signature);
    }
}
