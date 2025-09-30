    <?php

    use App\Http\Controllers\Website\BogCardController;
use App\Http\Controllers\Website\BogPaymentController;
use Illuminate\Support\Facades\Route;

// BOG Payment Callback - No CSRF protection needed as it's an external service callback
Route::post('/bog/callback', [BogPaymentController::class, 'handleCallback'])
    ->withoutMiddleware(['web', 'verify.csrf.token'])
    ->name('bog.callback');

// Create BOG order
Route::post('/bog/orders', [BogPaymentController::class, 'createOrder'])
    ->withoutMiddleware(['verify.csrf.token'])
    ->name('bog.orders.create');

// Get BOG order details
Route::get('/bog/orders/{orderId}', [BogPaymentController::class, 'orderDetails'])
    ->name('bog.orders.details');

// Save card for future payments
Route::post('/bog/orders/{orderId}/save-card', [BogPaymentController::class, 'saveCard'])
    ->name('bog.orders.save-card');

// Charge saved card for payment
Route::post('/bog/orders/{parentOrderId}/charge', [BogPaymentController::class, 'chargeCard'])
    ->name('bog.orders.charge-card');

// Card management routes
Route::prefix('bog/cards')->group(function () {
    // Save card for future payments
    Route::post('/save', [BogCardController::class, 'saveCard'])
        ->name('bog.cards.save');

    // List saved cards for user
    Route::get('/', [BogCardController::class, 'listCards'])
        ->name('bog.cards.list');

    // Delete saved card
    Route::delete('/{cardId}', [BogCardController::class, 'deleteCard'])
        ->name('bog.cards.delete');

    // Set default card
    Route::post('/{cardId}/set-default', [BogCardController::class, 'setDefaultCard'])
        ->name('bog.cards.set-default');
});

// Pay with saved card
Route::post('/bog/ecommerce/orders/{parentOrderId}/pay', [BogPaymentController::class, 'payWithSavedCard'])
    ->name('bog.ecommerce.orders.pay');

// Process automatic payment with saved card
Route::post('/bog/ecommerce/orders/{parentOrderId}/subscribe', [BogPaymentController::class, 'processAutomaticPayment'])
    ->name('bog.ecommerce.orders.subscribe');

// Confirm pre-authorization
Route::post('/bog/payment/authorization/approve/{orderId}', [BogPaymentController::class, 'confirmPreAuthorization'])
    ->name('bog.payment.authorization.approve');

// Reject pre-authorization
Route::post('/bog/payment/authorization/reject/{orderId}', [BogPaymentController::class, 'rejectPreAuthorization'])
    ->name('bog.payment.authorization.reject');
