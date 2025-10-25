# Controllers and Routes

## Controllers Included

### 1. BogPaymentController
Located at: `src/Controllers/BogPaymentController.php`

This controller handles all BOG payment operations including:
- Creating payment orders
- Handling payment callbacks
- Managing order details
- Saving cards for future payments
- Charging saved cards
- Payment history
- Pre-authorization operations

**Key Methods:**
- `createOrder()` - Create a new payment order
- `handleCallback()` - Handle BOG payment callbacks
- `orderDetails()` - Get order details
- `saveCard()` - Save card for future use
- `chargeCard()` - Charge a saved card
- `getUserPayments()` - Get user payment history

### 2. BogCardController
Located at: `src/Controllers/BogCardController.php`

This controller handles saved payment card management:
- Adding new cards manually
- Listing saved cards
- Deleting cards
- Setting default cards

**Key Methods:**
- `addCard()` - Add a card manually
- `listCards()` - List user's saved cards
- `deleteCard()` - Delete a saved card
- `setDefaultCard()` - Set a card as default

## Routes Included

The package includes a comprehensive routes file at `src/Routes/api.php` with the following routes:

### Payment Routes

```
POST   /bog/callback                     - Handle BOG callback
POST   /bog/orders                       - Create payment order
GET    /bog/orders/{orderId}             - Get order details
POST   /bog/orders/{orderId}/save-card   - Save card after payment
POST   /bog/orders/{parentOrderId}/charge - Charge saved card
GET    /bog/payments                     - Get payment history (authenticated)
```

### Card Management Routes (Authenticated)

```
POST   /bog/cards/add                    - Add new card
POST   /bog/cards/save                   - Save card from payment
GET    /bog/cards                        - List all saved cards
DELETE /bog/cards/{cardId}               - Delete a card
POST   /bog/cards/{cardId}/set-default   - Set default card
```

## Enabling/Disabling Routes

Routes are enabled by default. To disable them:

1. Edit `.env` file:
```env
BOG_ROUTES_ENABLED=false
```

2. Or edit `config/bog-payment.php`:
```php
'routes' => [
    'enabled' => false,
],
```

## Using Controllers Directly

If routes are disabled, you can use the controllers in your own routes:

```php
use Bog\Payment\Controllers\BogPaymentController;
use Bog\Payment\Controllers\BogCardController;

Route::post('/custom/payment', [BogPaymentController::class, 'createOrder']);
```

## Customization

All controllers are fully customizable. You can:
- Extend them in your application
- Override methods as needed
- Add custom middleware
- Add additional validation
