<?php

declare(strict_types=1);

/**
 * Standalone webhook receiver.
 *
 * Run with:
 *   php -S 127.0.0.1:8090 webhook-receiver.php
 *
 * It stores each incoming payload in sandbox/webhook-events.log.jsonl
 */

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST allowed'], JSON_PRETTY_PRINT);
    exit;
}

$raw = (string) file_get_contents('php://input');
$payload = json_decode($raw, true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON body'], JSON_PRETTY_PRINT);
    exit;
}

$logLine = [
    'received_at' => gmdate('c'),
    'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null,
    'event_type' => $payload['event_type'] ?? null,
    'event_id' => $payload['event_id'] ?? null,
    'meta' => $payload['meta'] ?? null,
    'resource_href' => $payload['resource_href'] ?? null,
    'payload' => $payload,
];

$logFile = __DIR__.'/webhook-events.log.jsonl';
file_put_contents($logFile, json_encode($logLine).PHP_EOL, FILE_APPEND);

http_response_code(200);
echo json_encode([
    'ok' => true,
    'message' => 'Webhook received',
    'stored_in' => 'sandbox/webhook-events.log.jsonl',
    'event_id' => $payload['event_id'] ?? null,
], JSON_PRETTY_PRINT);
