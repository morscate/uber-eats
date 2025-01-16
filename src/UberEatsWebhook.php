<?php

namespace Morscate\UberEats;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Add and maybe check the hmacSignature
 */
class UberEatsWebhook
{
    protected string $platform = 'uber-eats';

    public function __construct(
        protected string $eventName,
        protected string $storeId,
        protected ?string $resourceId,
        protected array $payload,
        protected array $headers,
    ) {
        //
    }

    public function platform(): string
    {
        return $this->platform;
    }

    public function eventName(): string
    {
        $eventName = Str::of($this->eventName)->lower()->replace('.', '-');

        return 'uber-eats-webhooks.'.$eventName->toString();
    }

    public function storeId(): ?string
    {
        return $this->storeId;
    }

    public function resourceId(): ?string
    {
        return $this->resourceId;
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public static function fromNotification(array $notification, array $headers): self
    {
        $storeId = Arr::get($notification, 'meta.user_id') ?? Arr::get($notification, 'store_id');
        $resourceId = Arr::get($notification, 'meta.resource_id');

        return new self(
            $notification['event_type'],
            $storeId,
            $resourceId,
            $notification,
            $headers
        );
    }
}
