<?php

// Bootstraps the Laravel app and calls BogPaymentController::bulkUpdateRentalStatus

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

// Prepare request payload - adjust product_id to an existing product in your DB
$payload = [
    'products' => [
        [
            'product_id' => 1,
            'rental_start_date' => date('Y-m-d'),
            'rental_end_date' => date('Y-m-d', strtotime('+3 days')),
        ],
    ],
];

$req = Request::create('/api/products/bulk-rental-status', 'POST', $payload);
// Resolve authenticated user via sanctum guard
$req->setUserResolver(function () {
    return \App\Models\User::first();
});

$controller = new \App\Http\Controllers\Website\BogPaymentController();
try {
    $response = $controller->bulkUpdateRentalStatus($req);
    // If response is a JsonResponse
    if (method_exists($response, 'getContent')) {
        echo $response->getContent();
    } else {
        var_export($response);
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
