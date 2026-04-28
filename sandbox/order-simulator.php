<?php

declare(strict_types=1);

/**
 * Uber Eats sandbox simulator.
 *
 * Endpoints:
 *   GET  ?action=menu
 *   GET  ?action=orders
 *   GET  ?action=get_order&order_id=<ORDER_ID>
 *   POST ?action=confirm_order   body: {"order_id":"...","webhook_url":"http://.../webhook-receiver.php"}
 */

const STORE_ID = 'bd1ed236-ee79-11ed-a05b-0242ac120003';
const STORE_NAME = "Uber's Pizza Palace";

const STATIC_ORDER_IDS = [
    'bd1ed236-ee79-11ed-a05b-0242ac12A001',
    'bd1ed236-ee79-11ed-a05b-0242ac12A002',
    'bd1ed236-ee79-11ed-a05b-0242ac12A003',
    'bd1ed236-ee79-11ed-a05b-0242ac12A004',
    'bd1ed236-ee79-11ed-a05b-0242ac12A005',
];

const MENU_FILE = __DIR__.'/../menu-store-1d5914b5.json';
const DATA_FILE = __DIR__.'/orders.data.json';

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'menu';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $menu = loadMenu();
    $store = loadDataStore();

    switch ($action) {
        case 'menu':
            echo json_encode(buildPickableMenu($menu), JSON_PRETTY_PRINT);
            break;

        case 'orders':
            echo json_encode([
                'store_id' => STORE_ID,
                'static_order_ids' => STATIC_ORDER_IDS,
                'confirmed_order_ids' => array_keys($store['confirmed_orders']),
            ], JSON_PRETTY_PRINT);
            break;

        case 'get_order':
            if ($method !== 'GET') {
                jsonError('Method not allowed', 405);
            }

            $orderId = trim((string) ($_GET['order_id'] ?? ''));
            if ($orderId === '') {
                jsonError('Missing order_id', 422);
            }

            if (!in_array($orderId, STATIC_ORDER_IDS, true)) {
                jsonError('order_id is not one of the static supported IDs', 404);
            }

            $orderResponse = buildOrderResponse($orderId, $menu);
            echo json_encode($orderResponse, JSON_PRETTY_PRINT);
            break;

        case 'confirm_order':
            if ($method !== 'POST') {
                jsonError('Method not allowed', 405);
            }

            $body = json_decode((string) file_get_contents('php://input'), true) ?: [];
            $orderId = trim((string) ($body['order_id'] ?? ''));
            $webhookUrl = trim((string) ($body['webhook_url'] ?? ''));

            if ($orderId === '' || $webhookUrl === '') {
                jsonError('order_id and webhook_url are required', 422);
            }

            if (!in_array($orderId, STATIC_ORDER_IDS, true)) {
                jsonError('order_id is not one of the static supported IDs', 404);
            }

            $eventPayload = buildWebhookPayload($orderId);
            $delivery = sendWebhook($webhookUrl, $eventPayload);

            $store['confirmed_orders'][$orderId] = [
                'confirmed_at' => gmdate('c'),
                'payment_method' => 'CASH',
                'webhook_url' => $webhookUrl,
                'webhook_event_id' => $eventPayload['event_id'],
                'webhook_http_code' => $delivery['http_code'],
                'webhook_response' => $delivery['response'],
            ];
            saveDataStore($store);

            echo json_encode([
                'message' => 'Cash order confirmed and webhook sent',
                'store_id' => STORE_ID,
                'order_id' => $orderId,
                'payment_method' => 'CASH',
                'webhook' => [
                    'url' => $webhookUrl,
                    'http_code' => $delivery['http_code'],
                    'response' => $delivery['response'],
                    'payload' => $eventPayload,
                ],
            ], JSON_PRETTY_PRINT);
            break;

        default:
            jsonError('Unknown action. Use menu, orders, get_order, confirm_order', 404);
    }
} catch (Throwable $e) {
    jsonError($e->getMessage(), 500);
}

function loadMenu(): array
{
    if (!is_file(MENU_FILE)) {
        throw new RuntimeException('Menu file not found: '.MENU_FILE);
    }

    $decoded = json_decode((string) file_get_contents(MENU_FILE), true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Invalid menu JSON');
    }

    return $decoded;
}

function buildPickableMenu(array $menu): array
{
    $items = indexItems($menu['items'] ?? []);
    $groups = indexModifierGroups($menu['modifier_groups'] ?? []);
    $categories = $menu['categories'] ?? [];

    $pickable = [];

    foreach ($categories as $category) {
        $categoryName = (string) ($category['title']['translations']['en'] ?? $category['id'] ?? 'Category');
        $entityRefs = $category['entities'] ?? [];

        $categoryItems = [];
        foreach ($entityRefs as $entity) {
            $itemId = (string) ($entity['id'] ?? '');
            if (!isset($items[$itemId])) {
                continue;
            }

            $item = $items[$itemId];
            $modifierSummary = [];
            foreach (($item['modifier_group_ids']['ids'] ?? []) as $modifierGroupId) {
                if (!isset($groups[$modifierGroupId])) {
                    continue;
                }

                $group = $groups[$modifierGroupId];
                $modifierSummary[] = [
                    'id' => $modifierGroupId,
                    'title' => (string) ($group['title']['translations']['en'] ?? $modifierGroupId),
                    'min' => (int) ($group['quantity_info']['quantity']['min_permitted'] ?? 0),
                    'max' => (int) ($group['quantity_info']['quantity']['max_permitted'] ?? 0),
                ];
            }

            $categoryItems[] = [
                'id' => $itemId,
                'title' => (string) ($item['title']['translations']['en'] ?? $itemId),
                'price' => moneyFromE5((int) ($item['price_info']['price'] ?? 0)),
                'modifier_groups' => $modifierSummary,
            ];
        }

        $pickable[] = [
            'id' => (string) ($category['id'] ?? ''),
            'name' => $categoryName,
            'items' => $categoryItems,
        ];
    }

    return [
        'store_id' => STORE_ID,
        'store_name' => STORE_NAME,
        'menu' => $pickable,
    ];
}

function buildOrderResponse(string $orderId, array $menu): array
{
    $combination = orderCombinationFor($orderId, $menu);
    $cartItems = [];
    $itemGrossTotalE5 = 0;

    foreach ($combination['items'] as $index => $selection) {
        $cartItem = mapSelectionToCartItem($selection, $index + 1);
        $cartItems[] = $cartItem;
        $itemGrossTotalE5 += $cartItem['line_total_e5'];
    }

    $taxE5 = (int) round($itemGrossTotalE5 * 0.07);
    $orderGrossE5 = $itemGrossTotalE5 + $taxE5;

    $displayId = substr($orderId, -5);
    $now = gmdate('Y-m-d\\TH:i:s.000\\Z');
    $later = gmdate('Y-m-d\\TH:i:s.000\\Z', time() + 1800);

    return [
        'order' => [
            'id' => $orderId,
            'display_id' => $displayId,
            'external_id' => 'UBER_EATS_ORDER_'.substr($displayId, -1),
            'state' => 'CREATED',
            'status' => 'SCHEDULED',
            'preparation_status' => 'PREPARING',
            'ordering_platform' => 'UBER_EATS',
            'fulfillment_type' => 'DELIVERY_BY_UBER',
            'scheduled_order_target_delivery_time_range' => [
                'start_time' => $now,
                'end_time' => $later,
            ],
            'store' => [
                'id' => STORE_ID,
                'name' => STORE_NAME,
                'partner_identifiers' => [
                    [
                        'value' => 'store1',
                        'type' => 'MERCHANT_STORE_ID',
                    ],
                ],
                'uber_merchant_type' => [
                    'type' => 'MERCHANT_TYPE_RESTAURANT',
                ],
            ],
            'customers' => [
                [
                    'id' => '9ae8779e-1cd7-5322-86b6-d7955afd051b',
                    'name' => [
                        'display_name' => 'Uber L',
                        'first_name' => 'Uber',
                        'last_name' => 'L',
                    ],
                    'order_history' => ['past_order_count' => 3],
                    'contact' => [
                        'phone' => [
                            'number' => '+1-800-999-9999',
                            'pin_code' => '888 52 337',
                            'country_iso2' => 'US',
                        ],
                    ],
                    'is_primary_customer' => true,
                    'can_respond_to_fulfillment_issues' => true,
                ],
            ],
            'deliveries' => [
                [
                    'id' => '1676a555-1a6f-4d49-be91-c9bb8f94af49',
                    'delivery_partner' => [
                        'id' => 'driver-1',
                        'name' => ['display_name' => 'Jason'],
                        'vehicle' => [
                            'type' => 'CAR',
                            'make' => 'Honda',
                            'model' => 'Accord',
                            'color' => 'red',
                            'license_plate' => 'T124224',
                            'is_autonomous' => false,
                        ],
                    ],
                    'status' => 'EN_ROUTE_TO_PICKUP',
                    'estimated_dropoff_time' => $later,
                    'interaction_type' => 'DELIVER_TO_DOOR',
                    'instructions' => 'Please do not ring doorbell.',
                ],
            ],
            'carts' => [
                [
                    'id' => 'cart-'.strtolower($displayId),
                    'items' => array_map(static fn (array $item): array => [
                        'id' => $item['id'],
                        'cart_item_id' => $item['cart_item_id'],
                        'customer_id' => $item['customer_id'],
                        'title' => $item['title'],
                        'external_data' => $item['external_data'],
                        'quantity' => [
                            'amount' => $item['quantity'],
                            'unit' => 'PIECE',
                        ],
                        'default_quantity' => [
                            'amount' => 1,
                            'unit' => 'PIECE',
                        ],
                        'customer_request' => [
                            'special_instructions' => 'Add extra sauce',
                        ],
                        'selected_modifier_groups' => $item['selected_modifier_groups'],
                        'picture_url' => $item['picture_url'],
                    ], $cartItems),
                    'special_instructions' => 'Please add extra sauce.',
                    'include_single_use_items' => true,
                ],
            ],
            'payment' => [
                'payment_detail' => [
                    'currency_code' => 'USD',
                    'order_total' => amountBlock($orderGrossE5),
                    'item_charges' => [
                        'total' => amountBlock($itemGrossTotalE5),
                        'subtotal_including_promos' => amountBlock($itemGrossTotalE5),
                        'price_breakdown' => array_map(static fn (array $item): array => [
                            'cart_item_id' => $item['cart_item_id'],
                            'price_type' => 'ITEM',
                            'quantity' => [
                                'amount' => $item['quantity'],
                                'unit' => 'PIECE',
                            ],
                            'total' => amountBlock($item['line_total_e5']),
                            'unit' => amountBlock($item['unit_price_e5']),
                        ], $cartItems),
                    ],
                    'fees' => ['total' => amountBlock(0), 'details' => []],
                    'tips' => ['total' => amountBlock(0)],
                    'promotions' => [
                        'total' => amountBlock(0),
                        'details' => [],
                        'order_total_excluding_promos' => amountBlock($orderGrossE5),
                    ],
                    'adjustment' => ['total' => amountBlock(0)],
                    'cash_amount_due' => amountBlock($orderGrossE5),
                ],
            ],
            'is_order_accuracy_risk' => false,
            'store_instructions' => 'add example ketchup',
            'preparation_time' => [
                'ready_for_pickup_time_secs' => 500,
                'source' => 'PREDICTED_BY_UBER',
                'ready_for_pickup_time' => $now,
            ],
            'created_time' => $now,
            'completed_time' => null,
            'has_membership_pass' => false,
            'support_contact' => [
                'number' => 1234567890,
                'verification_pin' => 12345678,
                'verification_pin_expiry' => gmdate('Y-m-d\\TH:i:s\\Z', time() + 7200),
            ],
        ],
    ];
}

function buildWebhookPayload(string $orderId): array
{
    return [
        'event_type' => 'orders.notification',
        'event_id' => uuidV4(),
        'event_time' => time(),
        'meta' => [
            'resource_id' => $orderId,
            'status' => 'pos',
            'user_id' => STORE_ID,
        ],
        'resource_href' => 'https://api.uber.com/v2/eats/order/'.$orderId,
    ];
}

function sendWebhook(string $url, array $payload): array
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new RuntimeException('Invalid webhook_url');
    }

    $ch = curl_init($url);
    if ($ch === false) {
        throw new RuntimeException('Unable to initialize cURL');
    }

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_POSTFIELDS => json_encode($payload),
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Webhook delivery failed: '.$error);
    }

    curl_close($ch);

    return [
        'http_code' => $httpCode,
        'response' => $response,
    ];
}

function orderCombinationFor(string $orderId, array $menu): array
{
    $items = indexItems($menu['items'] ?? []);
    $groups = indexModifierGroups($menu['modifier_groups'] ?? []);

    $plans = [
        'bd1ed236-ee79-11ed-a05b-0242ac12A001' => [
            ['item' => 'item_classic_burger_s1', 'qty' => 1],
            ['item' => 'item_fries_s1', 'qty' => 1],
            ['item' => 'item_cola_s1', 'qty' => 1],
        ],
        'bd1ed236-ee79-11ed-a05b-0242ac12A002' => [
            ['item' => 'item_bbq_burger_s1', 'qty' => 1],
            ['item' => 'item_onion_rings_s1', 'qty' => 1],
            ['item' => 'item_milkshake_s1', 'qty' => 1],
        ],
        'bd1ed236-ee79-11ed-a05b-0242ac12A003' => [
            ['item' => 'item_classic_burger_s1', 'qty' => 2],
            ['item' => 'item_cola_s1', 'qty' => 2],
        ],
        'bd1ed236-ee79-11ed-a05b-0242ac12A004' => [
            ['item' => 'item_bbq_burger_s1', 'qty' => 1],
            ['item' => 'item_fries_s1', 'qty' => 2],
            ['item' => 'item_cola_s1', 'qty' => 1],
        ],
        'bd1ed236-ee79-11ed-a05b-0242ac12A005' => [
            ['item' => 'item_classic_burger_s1', 'qty' => 1],
            ['item' => 'item_onion_rings_s1', 'qty' => 1],
            ['item' => 'item_milkshake_s1', 'qty' => 1],
        ],
    ];

    $plan = $plans[$orderId] ?? [];
    $selected = [];

    foreach ($plan as $slot) {
        $itemId = $slot['item'];
        if (!isset($items[$itemId])) {
            continue;
        }

        $item = $items[$itemId];
        $selectedGroups = [];

        foreach (($item['modifier_group_ids']['ids'] ?? []) as $groupId) {
            if (!isset($groups[$groupId])) {
                continue;
            }

            $group = $groups[$groupId];
            $options = $group['modifier_options'] ?? [];
            $min = (int) ($group['quantity_info']['quantity']['min_permitted'] ?? 0);

            $picked = [];
            for ($i = 0; $i < max(1, $min); $i++) {
                if (!isset($options[$i]['id']) || !isset($items[$options[$i]['id']])) {
                    continue;
                }

                $modId = (string) $options[$i]['id'];
                $modItem = $items[$modId];

                $picked[] = [
                    'id' => $modId,
                    'title' => (string) ($modItem['title']['translations']['en'] ?? $modId),
                    'price_e5' => (int) ($modItem['price_info']['price'] ?? 0),
                ];
            }

            $selectedGroups[] = [
                'id' => $groupId,
                'title' => (string) ($group['title']['translations']['en'] ?? $groupId),
                'selected_items' => $picked,
                'removed_items' => [],
            ];
        }

        $selected[] = [
            'id' => $itemId,
            'title' => (string) ($item['title']['translations']['en'] ?? $itemId),
            'external_data' => $itemId,
            'picture_url' => (string) ($item['image_url'] ?? ''),
            'base_price_e5' => (int) ($item['price_info']['price'] ?? 0),
            'quantity' => (int) ($slot['qty'] ?? 1),
            'selected_modifier_groups' => $selectedGroups,
        ];
    }

    return ['items' => $selected];
}

function mapSelectionToCartItem(array $selection, int $sequence): array
{
    $modifierTotalE5 = 0;
    foreach ($selection['selected_modifier_groups'] as $group) {
        foreach ($group['selected_items'] as $modifier) {
            $modifierTotalE5 += (int) ($modifier['price_e5'] ?? 0);
        }
    }

    $unitE5 = (int) $selection['base_price_e5'] + $modifierTotalE5;
    $lineTotalE5 = $unitE5 * (int) $selection['quantity'];

    return [
        'id' => $selection['id'],
        'cart_item_id' => sprintf('cart-item-%04d', $sequence),
        'customer_id' => '092400ec-ee7b-11ed-a05b-0242ac120003',
        'title' => $selection['title'],
        'external_data' => $selection['external_data'],
        'picture_url' => $selection['picture_url'],
        'quantity' => (int) $selection['quantity'],
        'selected_modifier_groups' => array_map(static fn (array $group): array => [
            'id' => $group['id'],
            'title' => $group['title'],
            'selected_items' => array_map(static fn (array $item): array => [
                'id' => $item['id'],
                'title' => $item['title'],
            ], $group['selected_items']),
            'removed_items' => [],
        ], $selection['selected_modifier_groups']),
        'unit_price_e5' => $unitE5,
        'line_total_e5' => $lineTotalE5,
    ];
}

function loadDataStore(): array
{
    if (!is_file(DATA_FILE)) {
        return ['confirmed_orders' => []];
    }

    $decoded = json_decode((string) file_get_contents(DATA_FILE), true);
    if (!is_array($decoded)) {
        return ['confirmed_orders' => []];
    }

    $decoded['confirmed_orders'] ??= [];

    return $decoded;
}

function saveDataStore(array $data): void
{
    file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

function indexItems(array $items): array
{
    $result = [];
    foreach ($items as $item) {
        if (isset($item['id'])) {
            $result[(string) $item['id']] = $item;
        }
    }

    return $result;
}

function indexModifierGroups(array $groups): array
{
    $result = [];
    foreach ($groups as $group) {
        if (isset($group['id'])) {
            $result[(string) $group['id']] = $group;
        }
    }

    return $result;
}

function amountBlock(int $amountE5): array
{
    return [
        'display_amount' => formatCurrency($amountE5),
        'net' => moneyFromE5($amountE5),
        'tax' => moneyFromE5(0),
        'gross' => moneyFromE5($amountE5),
        'is_tax_inclusive' => true,
    ];
}

function moneyFromE5(int $amountE5): array
{
    return [
        'amount_e5' => $amountE5,
        'currency_code' => 'USD',
        'formatted' => formatCurrency($amountE5),
    ];
}

function formatCurrency(int $amountE5): string
{
    $amount = $amountE5 / 100;

    return '$'.number_format($amount, 2);
}

function uuidV4(): string
{
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

    $hex = bin2hex($bytes);

    return sprintf(
        '%s-%s-%s-%s-%s',
        substr($hex, 0, 8),
        substr($hex, 8, 4),
        substr($hex, 12, 4),
        substr($hex, 16, 4),
        substr($hex, 20, 12)
    );
}

function jsonError(string $message, int $status): void
{
    http_response_code($status);
    echo json_encode(['error' => $message], JSON_PRETTY_PRINT);
    exit;
}
