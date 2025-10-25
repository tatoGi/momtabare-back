# Exceptions

The BOG Payment package includes custom exceptions for better error handling.

## Available Exceptions

### 1. BogPaymentException (Base Exception)

Base exception class for all BOG payment errors.

```php
use Bog\Payment\Exceptions\BogPaymentException;

throw new BogPaymentException('Custom error message', 500);
```

### 2. AuthenticationException

Thrown when BOG authentication fails.

```php
use Bog\Payment\Exceptions\AuthenticationException;

try {
    $token = $auth->getAccessToken();
    if (!$token) {
        throw new AuthenticationException('Failed to get access token');
    }
} catch (AuthenticationException $e) {
    // Handle auth failure
}
```

### 3. OrderCreationException

Thrown when order creation fails.

```php
use Bog\Payment\Exceptions\OrderCreationException;

try {
    $order = $payment->createOrder($token, $data);
    if (!$order) {
        throw new OrderCreationException('Failed to create order');
    }
} catch (OrderCreationException $e) {
    // Handle order creation failure
}
```

### 4. CallbackException

Thrown when callback processing fails.

```php
use Bog\Payment\Exceptions\CallbackException;

try {
    // Process callback
    if (!$processed) {
        throw new CallbackException('Callback processing failed');
    }
} catch (CallbackException $e) {
    // Handle callback error
}
```

### 5. CardException

Thrown when card operations fail.

```php
use Bog\Payment\Exceptions\CardException;

try {
    if (!$cardSaved) {
        throw new CardException('Failed to save card');
    }
} catch (CardException $e) {
    // Handle card error
}
```

## Exception Response Format

All exceptions return a consistent JSON response:

```json
{
    "success": false,
    "message": "Error message here",
    "error_code": "ExceptionClassName"
}
```

## Usage in Controllers

```php
use Bog\Payment\Exceptions\AuthenticationException;
use Bog\Payment\Exceptions\OrderCreationException;

public function createOrder(Request $request)
{
    try {
        $token = $this->bogAuth->getAccessToken();
        
        if (!$token) {
            throw new AuthenticationException('Authentication failed');
        }
        
        $order = $this->bogPayment->createOrder($token['access_token'], $data);
        
        if (!$order) {
            throw new OrderCreationException('Order creation failed');
        }
        
        return response()->json(['success' => true, 'data' => $order]);
        
    } catch (AuthenticationException | OrderCreationException $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], $e->getCode());
    }
}
```

## Custom Exception Handling

You can register custom exception handlers in your `app/Exceptions/Handler.php`:

```php
public function register()
{
    $this->renderable(function (\Bog\Payment\Exceptions\BogPaymentException $e, $request) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'error_code' => class_basename($e),
        ], $e->getCode());
    });
}
```

## Exception Codes

- `BogPaymentException`: 500
- `AuthenticationException`: 401
- `OrderCreationException`: 400
- `CallbackException`: 400
- `CardException`: 400

## Best Practices

1. **Catch specific exceptions** instead of using generic catch-all
2. **Log exceptions** for debugging
3. **Return user-friendly messages** to frontend
4. **Use appropriate HTTP status codes**
5. **Include error codes** for programmatic handling

## Example: Complete Error Handling

```php
use Bog\Payment\Exceptions\BogPaymentException;
use Bog\Payment\Exceptions\AuthenticationException;
use Bog\Payment\Exceptions\OrderCreationException;
use Illuminate\Support\Facades\Log;

public function processPayment(Request $request)
{
    try {
        // Authenticate
        $token = $this->bogAuth->getAccessToken();
        if (!$token) {
            throw new AuthenticationException();
        }
        
        // Create order
        $order = $this->bogPayment->createOrder($token['access_token'], $data);
        if (!$order) {
            throw new OrderCreationException();
        }
        
        return response()->json(['success' => true, 'data' => $order]);
        
    } catch (AuthenticationException $e) {
        Log::error('BOG Auth Failed: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Authentication failed'], 401);
        
    } catch (OrderCreationException $e) {
        Log::error('Order Creation Failed: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Order creation failed'], 400);
        
    } catch (\Exception $e) {
        Log::error('Unexpected Error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
    }
}
```
