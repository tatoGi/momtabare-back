# Bulk Product Rental Status Update API

## Endpoint
```
POST /api/products/bulk-rental-status
```

## Authentication
Requires Sanctum authentication token:
```
Authorization: Bearer {your_token}
```

## Purpose
This endpoint allows the frontend to bulk update product rental status after a successful payment. It marks products as ordered/rented and sets rental dates.

## Request Body

```json
{
  "products": [
    {
      "product_id": 123,
      "rental_start_date": "2025-10-17",
      "rental_end_date": "2025-10-24",
      "quantity": 1
    },
    {
      "product_id": 456,
      "rental_start_date": "2025-10-17",
      "rental_end_date": "2025-10-31",
      "quantity": 2
    }
  ],
  "payment_id": 789  // Optional: BOG payment ID for tracking
}
```

## Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `products` | array | Yes | Array of product rental data |
| `products.*.product_id` | integer | Yes | Product ID (must exist in database) |
| `products.*.rental_start_date` | date | No | Rental start date (YYYY-MM-DD) |
| `products.*.rental_end_date` | date | No | Rental end date (must be >= start_date) |
| `products.*.quantity` | integer | No | Quantity being rented (default: 1) |
| `payment_id` | integer | No | BOG payment ID for reference |

## Response

### Success (200 OK)
```json
{
  "success": true,
  "message": "Product rental status updated successfully",
  "updated_products": [
    {
      "product_id": 123,
      "status": "updated",
      "is_rented": true,
      "rental_start_date": "2025-10-17 00:00:00",
      "rental_end_date": "2025-10-24 00:00:00"
    },
    {
      "product_id": 456,
      "status": "updated",
      "is_rented": true,
      "rental_start_date": "2025-10-17 00:00:00",
      "rental_end_date": "2025-10-31 00:00:00"
    }
  ],
  "errors": [],
  "summary": {
    "total_requested": 2,
    "successfully_updated": 2,
    "failed": 0
  }
}
```

### Partial Success (200 OK)
If some products update successfully but others fail:
```json
{
  "success": true,
  "message": "Product rental status updated successfully",
  "updated_products": [
    {
      "product_id": 123,
      "status": "updated",
      "is_rented": true,
      "rental_start_date": "2025-10-17 00:00:00",
      "rental_end_date": "2025-10-24 00:00:00"
    }
  ],
  "errors": [
    "Product 456 not found",
    "Failed to update product 789: Database error"
  ],
  "summary": {
    "total_requested": 3,
    "successfully_updated": 1,
    "failed": 2
  }
}
```

### Validation Error (422 Unprocessable Entity)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "products.0.product_id": [
      "The products.0.product_id field is required."
    ],
    "products.1.rental_end_date": [
      "The products.1.rental_end_date must be a date after or equal to products.1.rental_start_date."
    ]
  }
}
```

### Unauthorized (401 Unauthorized)
```json
{
  "success": false,
  "message": "User not authenticated"
}
```

### Server Error (500 Internal Server Error)
```json
{
  "success": false,
  "message": "Failed to update product rental status",
  "error": "Detailed error message"
}
```

## Product Update Logic

When a product is updated:

1. **Always set:**
   - `is_ordered` = true
   - `ordered_at` = current timestamp
   - `ordered_by` = authenticated user's ID

2. **If rental dates provided:**
   - `is_rented` = true
   - `rented_at` = current timestamp
   - `rental_start_date` = provided start date
   - `rental_end_date` = provided end date
   - `rented_by` = null (FK constraint to users table, not web_users)

## Usage Example

### JavaScript/TypeScript
```typescript
async function updateProductRentalStatus(products: any[], paymentId?: number) {
  try {
    const response = await fetch('https://admin.momtabare.com/api/products/bulk-rental-status', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${yourAuthToken}`,
      },
      body: JSON.stringify({
        products: products,
        payment_id: paymentId,
      }),
    });

    const result = await response.json();
    
    if (result.success) {
      console.log('✅ Updated products:', result.updated_products);
      if (result.errors.length > 0) {
        console.warn('⚠️ Some products failed:', result.errors);
      }
    } else {
      console.error('❌ Update failed:', result.message);
    }
    
    return result;
  } catch (error) {
    console.error('❌ Request failed:', error);
    throw error;
  }
}

// Usage
const products = [
  {
    product_id: 123,
    rental_start_date: '2025-10-17',
    rental_end_date: '2025-10-24',
  }
];

await updateProductRentalStatus(products, 789);
```

### cURL
```bash
curl -X POST https://admin.momtabare.com/api/products/bulk-rental-status \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your_token_here" \
  -d '{
    "products": [
      {
        "product_id": 123,
        "rental_start_date": "2025-10-17",
        "rental_end_date": "2025-10-24",
        "quantity": 1
      }
    ],
    "payment_id": 789
  }'
```

## Notes

1. **Graceful Failure**: The endpoint processes all products even if some fail. Check the `errors` array for failures.

2. **Authentication Required**: You must be authenticated as a web_user (Sanctum token).

3. **Rental Dates Optional**: If you don't provide rental dates, the product will be marked as ordered but not rented.

4. **Database Constraints**: The `rented_by` field has a foreign key to the `users` table (not `web_users`), so it's set to null. The actual user is tracked in `ordered_by`.

5. **Logging**: All updates are logged for debugging. Check Laravel logs for detailed information.

## Related Endpoints

- `POST /api/bog/orders` - Create new payment order
- `POST /api/bog/ecommerce/orders/{id}/pay` - Pay with saved card
- `GET /api/bog/payments` - Get user's payment history

## Backend Implementation

The endpoint is handled by:
- **Controller**: `App\Http\Controllers\Website\BogPaymentController::bulkUpdateRentalStatus()`
- **Route**: `routes/website/products.php`
- **Middleware**: `auth:sanctum`

## When to Use

This endpoint should be called:
- ✅ After successful payment confirmation from BOG
- ✅ When the backend's automatic `markProductsAsOrdered()` fails
- ✅ For manual product rental status updates
- ❌ Not needed if payment callback already marked products as ordered
