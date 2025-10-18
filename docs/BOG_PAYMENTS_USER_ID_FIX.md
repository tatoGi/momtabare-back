# BOG Payments user_id Fix - October 17, 2025

## Problem
The `bog_payments.user_id` column was **always NULL** because of a foreign key constraint mismatch:
- `bog_payments.user_id` had FK to `users` table
- But customers are stored in `web_users` table
- Controllers were setting `user_id = null` to avoid FK constraint violations

## Root Cause
```php
// Old code in createOrder() and payWithSavedCard()
'user_id' => null, // FK constraint issue: points to users table, not web_users
```

The database migration created a foreign key to the wrong table:
```php
$table->foreign('user_id')
    ->references('id')
    ->on('users')  // ❌ WRONG TABLE - should be web_users
    ->onDelete('cascade');
```

## Solution Implemented

### 1. Database Migration
Created migration to change FK constraint from `users` to `web_users`:

**File**: `database/migrations/2025_10_17_180000_change_bog_payments_user_id_fk_to_web_users.php`

```php
Schema::table('bog_payments', function (Blueprint $table) {
    // Drop the existing foreign key constraint to users table
    $table->dropForeign(['user_id']);
    
    // Add new foreign key constraint to web_users table
    $table->foreign('user_id')
        ->references('id')
        ->on('web_users')  // ✅ CORRECT TABLE
        ->onDelete('cascade')
        ->onUpdate('restrict');
});
```

### 2. Controller Updates

#### createOrder() Method
**Before**:
```php
'user_id' => null, // FK constraint issue
```

**After**:
```php
'user_id' => $validated['user_id'] ?? $user->id ?? null, // Now points to web_users table
```

#### payWithSavedCard() Method
**Before**:
```php
'user_id' => null, // FK constraint issue
```

**After**:
```php
'user_id' => $user->id, // Now correctly points to web_users table
```

### 3. Model Updates

Updated `BogPayment` model to clarify relationships:

**File**: `app/Models/BogPayment.php`

```php
/**
 * Get the web user (customer) who made this payment
 * Note: user_id now references web_users table (FK updated in migration)
 */
public function user()
{
    return $this->belongsTo(WebUser::class, 'user_id');
}

/**
 * Alias for user() relationship for clarity
 * @deprecated Use user() instead - kept for backward compatibility
 */
public function webUser()
{
    return $this->belongsTo(WebUser::class, 'user_id');
}
```

## Files Modified

1. **Database Migration**:
   - `database/migrations/2025_10_17_180000_change_bog_payments_user_id_fk_to_web_users.php` (NEW)

2. **Controller**:
   - `app/Http/Controllers/Website/BogPaymentController.php`
     - Updated `createOrder()` method (line ~188)
     - Updated `payWithSavedCard()` method (line ~619)

3. **Model**:
   - `app/Models/BogPayment.php`
     - Updated `user()` relationship
     - Kept `webUser()` for backward compatibility

4. **Documentation**:
   - `docs/BOG_PAYMENTS_USER_ID_FIX.md` (this file)

## Testing Results

### Before Fix
```sql
SELECT id, bog_order_id, user_id FROM bog_payments;
```
```
| id | bog_order_id        | user_id |
|----|---------------------|---------|
| 2  | test_1760718250     | NULL    | ❌
| 3  | saved_card_...      | NULL    | ❌
```

### After Fix
```sql
SELECT id, bog_order_id, user_id FROM bog_payments;
```
```
| id | bog_order_id           | user_id |
|----|------------------------|---------|
| 4  | test_with_user_1760... | 45      | ✅
```

### Test Code
```php
$user = \App\Models\WebUser::first();

$payment = \App\Models\BogPayment::create([
    'bog_order_id' => 'test_with_user_' . time(),
    'user_id' => $user->id, // ✅ Works now!
    'amount' => 99.99,
    'currency' => 'GEL',
    'status' => 'created',
    // ...
]);

echo $payment->user->email; // ✅ Works: tato.laperashvili95@gmail.com
```

## Backward Compatibility

The fix maintains backward compatibility in two ways:

1. **Dual Query in getUserPayments()**:
   ```php
   ->where(function($query) use ($user) {
       // New way: Direct user_id lookup
       $query->where('user_id', $user->id)
           // Old way: JSON payload lookup for old records
           ->orWhereRaw("JSON_EXTRACT(request_payload, '$.web_user_id') = ?", [$user->id]);
   })
   ```

2. **Dual Storage**:
   - `user_id` column: New records (after fix)
   - `request_payload.web_user_id`: All records (old and new)

## Benefits

1. ✅ **Proper Data Integrity**: FK constraint ensures valid user references
2. ✅ **Easier Queries**: Direct `WHERE user_id = ?` instead of JSON_EXTRACT
3. ✅ **Better Performance**: Indexed FK column vs. JSON extraction
4. ✅ **Cleaner Code**: No more `user_id = null` workarounds
5. ✅ **Relationship Loading**: Can use Eloquent relationships properly

## Usage Examples

### Create Payment with User
```php
$user = auth()->user(); // Web user (Sanctum)

$payment = BogPayment::create([
    'bog_order_id' => $orderId,
    'user_id' => $user->id, // ✅ Saved correctly
    'amount' => 100.00,
    'currency' => 'GEL',
    'status' => 'created',
]);
```

### Get User's Payments (Old Way - Still Works)
```php
$payments = BogPayment::whereRaw(
    "JSON_EXTRACT(request_payload, '$.web_user_id') = ?", 
    [$userId]
)->get();
```

### Get User's Payments (New Way - Recommended)
```php
$payments = BogPayment::where('user_id', $userId)->get();
```

### Load User Relationship
```php
$payment = BogPayment::with('user')->find($id);
echo $payment->user->email; // ✅ Works now
```

## Migration Instructions for Production

1. **Backup Database**:
   ```bash
   mysqldump -u root -p momtabare_back > backup_before_fk_change.sql
   ```

2. **Run Migration**:
   ```bash
   php artisan migrate
   ```

3. **Verify FK Change**:
   ```sql
   SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME 
   FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
   WHERE TABLE_NAME = 'bog_payments' 
   AND COLUMN_NAME = 'user_id';
   -- Should show: bog_payments_user_id_foreign -> web_users
   ```

4. **Update Existing Records** (Optional):
   ```php
   // Backfill user_id from request_payload for old records
   $payments = BogPayment::whereNull('user_id')->get();
   
   foreach ($payments as $payment) {
       $webUserId = $payment->request_payload['web_user_id'] ?? null;
       if ($webUserId && WebUser::find($webUserId)) {
           $payment->update(['user_id' => $webUserId]);
       }
   }
   ```

5. **Test Payment Creation**:
   - Create new order
   - Pay with saved card
   - Verify user_id is saved
   - Check getUserPayments returns correct data

## Rollback Instructions

If you need to rollback:

```bash
php artisan migrate:rollback --step=1
```

This will:
1. Drop FK to `web_users`
2. Restore FK to `users`
3. Set affected `user_id` values back to NULL

## Notes

- Old payment records will still have `user_id = NULL`
- This is OK because `getUserPayments()` checks both `user_id` and JSON payload
- Consider running backfill script (step 4 above) to update old records
- The `request_payload.web_user_id` is still stored for backward compatibility

## Status

✅ **Migration Run**: October 17, 2025  
✅ **Tests Passed**: Payment creation, user relationship loading  
✅ **Backward Compatible**: Old records still accessible  
✅ **Ready for Production**

---

**Last Updated**: October 17, 2025  
**Author**: AI Assistant  
**Status**: ✅ Complete and Tested
