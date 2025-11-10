    <?php

    use App\Http\Controllers\Website\BogCardController;
use App\Http\Controllers\Website\BogPaymentController;
use Illuminate\Support\Facades\Route;

// BOG Payment Callback - No CSRF protection needed as it's an external service callback
Route::post('/bog/callback', [BogPaymentController::class, 'handleCallback'])
    ->withoutMiddleware(['web', 'verify.csrf.token'])
    ->name('website.bog.callback');

// Create BOG order
Route::post('/bog/orders', [BogPaymentController::class, 'createOrder'])
    ->withoutMiddleware(['verify.csrf.token'])
    ->name('website.bog.orders.create');

// Get BOG order details
Route::get('/bog/orders/{orderId}', [BogPaymentController::class, 'orderDetails'])
    ->name('website.bog.orders.details');

// Save card for future payments
Route::post('/bog/orders/{orderId}/save-card', [BogPaymentController::class, 'saveCard'])
    ->name('website.bog.orders.save-card');

// Charge saved card for payment
Route::post('/bog/orders/{parentOrderId}/charge', [BogPaymentController::class, 'chargeCard'])
    ->name('website.bog.orders.charge-card');

// Card management routes
Route::prefix('bog/cards')->middleware('auth:sanctum')->group(function () {
    // Add a new card manually (BOG, Mastercard, Visa, etc.)
    Route::post('/add', [BogCardController::class, 'addCard'])
        ->name('website.bog.cards.add');

    // Save card for future payments (from payment flow)
    Route::post('/save', [BogCardController::class, 'saveCard'])
        ->name('website.bog.cards.save');

    // List saved cards for user
    Route::get('/', [BogCardController::class, 'listCards'])
        ->name('website.bog.cards.list');

    // Delete saved card
    Route::delete('/{cardId}', [BogCardController::class, 'deleteCard'])
        ->name('website.bog.cards.delete');

    // Set default card
    Route::post('/{cardId}/set-default', [BogCardController::class, 'setDefaultCard'])
        ->name('website.bog.cards.set-default');
});

// Payment history and management
Route::prefix('bog/payments')->middleware('auth:sanctum')->group(function () {
    // Get user's payment history
    Route::get('/', [BogPaymentController::class, 'getUserPayments'])
        ->name('website.bog.payments.list');
});

// Pay with saved card
Route::post('/bog/ecommerce/orders/{parentOrderId}/pay', [BogPaymentController::class, 'payWithSavedCard'])
    ->name('website.bog.ecommerce.orders.pay');

// Process automatic payment with saved card
Route::post('/bog/ecommerce/orders/{parentOrderId}/subscribe', [BogPaymentController::class, 'processAutomaticPayment'])
    ->name('website.bog.ecommerce.orders.subscribe');

// Confirm pre-authorization
Route::post('/bog/payment/authorization/approve/{orderId}', [BogPaymentController::class, 'confirmPreAuthorization'])
    ->name('website.bog.payment.authorization.approve');

// Reject pre-authorization
Route::post('/bog/payment/authorization/reject/{orderId}', [BogPaymentController::class, 'rejectPreAuthorization'])
    ->name('website.bog.payment.authorization.reject');
