# Product Name Translation Fix

## Issue
Product names (`name_ka`, `name_en`, `name_ru`) were returning empty strings in the `getUserPayments` API response.

**Frontend Error**:
```javascript
{
  "products": [
    {
      "id": 1,
      "name_ka": "",  // ❌ Empty
      "name_en": "",  // ❌ Empty
      "name_ru": "",  // ❌ Empty
      "slug": "test",
      "price": 0.02,
      // ...
    }
  ]
}
```

## Root Cause
The `Product` model uses **Astrotomic Translatable** with the attribute name **`title`**, not `name`.

**Database Schema**:
- Table: `product_translations`
- Translatable attributes: `title`, `slug`, `description`, `brand`, `location`, `color`
- No `name` column exists!

**Incorrect Code** (in `BogPaymentController::getUserPayments`):
```php
// ❌ Wrong: Trying to access 'name' which doesn't exist
$nameKa = $product->translate('ka')->name ?? '';
$nameEn = $product->translate('en')->name ?? '';
$nameRu = $product->translate('ru')->name ?? '';
```

## Solution
Changed the code to access **`title`** instead of `name`:

```php
// ✅ Correct: Access 'title' which exists in product_translations table
$nameKa = $product->translate('ka')->title ?? '';
$nameEn = $product->translate('en')->title ?? '';
$nameRu = $product->translate('ru')->title ?? '';
```

## Files Modified

### BogPaymentController.php
**File**: `app/Http/Controllers/Website/BogPaymentController.php`

**Method**: `getUserPayments()` (lines ~988-1002)

**Change**:
```diff
- // Get product name in all languages (translatable attributes)
+ // Get product title in all languages (translatable attributes)
+ // Note: Product model uses 'title' not 'name'
  $nameKa = '';
  $nameEn = '';
  $nameRu = '';

  try {
      // Astrotomic Translatable provides translate() method
-     $nameKa = $product->translate('ka')->name ?? '';
-     $nameEn = $product->translate('en')->name ?? '';
-     $nameRu = $product->translate('ru')->name ?? '';
+     $nameKa = $product->translate('ka')->title ?? '';
+     $nameEn = $product->translate('en')->title ?? '';
+     $nameRu = $product->translate('ru')->title ?? '';
  } catch (\Exception $e) {
      // Fallback: try direct property access
-     $nameKa = $product->name ?? '';
-     $nameEn = $product->name ?? '';
-     $nameRu = $product->name ?? '';
+     $nameKa = $product->title ?? '';
+     $nameEn = $product->title ?? '';
+     $nameRu = $product->title ?? '';
  }
```

## Verification

### Database Check
```sql
SELECT id, product_id, locale, title 
FROM product_translations 
WHERE product_id = 1;
```

**Result**:
```
| id | product_id | locale | title |
|----|------------|--------|-------|
| 2  | 1          | en     | test  |
| 1  | 1          | ka     | test  |
```

### Tinker Test
```php
$payment = \App\Models\BogPayment::with('products')->find(12);
$product = $payment->products->first();
return [
    'product_id' => $product->id,
    'title_ka' => $product->translate('ka')->title ?? 'null',
    'title_en' => $product->translate('en')->title ?? 'null',
    'title_ru' => $product->translate('ru')->title ?? 'null',
];
```

**Result**:
```php
[
    'product_id' => 1,
    'title_ka' => 'test',  // ✅
    'title_en' => 'test',  // ✅
    'title_ru' => 'null',  // ⚠️ No Russian translation exists
]
```

## Expected API Response (After Fix)

```json
{
  "success": true,
  "data": [
    {
      "id": 12,
      "bog_order_id": "saved_card_68f2945dd1ba2_1760728157",
      "products": [
        {
          "id": 1,
          "name_ka": "test",  // ✅ Now populated
          "name_en": "test",  // ✅ Now populated
          "name_ru": "",      // ⚠️ Empty (no Russian translation)
          "slug": "test",
          "price": 0.02,
          "images": [...],
          "quantity": 1,
          "unit_price": 0.02,
          "total_price": 0.02
        }
      ]
    }
  ]
}
```

## Important Notes

1. **Response Field Names**: The API response still uses `name_ka`, `name_en`, `name_ru` to match the frontend expectations. Only the **source** changed from `name` to `title`.

2. **Missing Translations**: If a product doesn't have a translation for a specific locale, it will return an empty string (e.g., `name_ru: ""`).

3. **Product Model Configuration**:
   ```php
   // app/Models/Product.php
   public $translatedAttributes = [
       'title',        // ✅ Used
       'slug', 
       'description', 
       'brand', 
       'location', 
       'color'
   ];
   ```

4. **Frontend Compatibility**: No frontend changes needed. The API response structure remains the same, only the values are now populated correctly.

## Testing Checklist

- [x] Fixed code to use `title` instead of `name`
- [x] Verified translations exist in database
- [x] Tested translation loading with tinker
- [x] Cleared application cache
- [ ] Test API endpoint with real frontend request
- [ ] Verify Russian translations show empty string when missing
- [ ] Confirm all languages load correctly in frontend UI

## Related Issues

This fix is part of the larger BOG Payment system fixes:
1. ✅ getUserPayments 500 error (translation loading)
2. ✅ user_id not saving (FK constraint)
3. ✅ BOG API base URL missing
4. ✅ **Product names not loading** (this fix)

---

**Status**: ✅ Fixed  
**Date**: October 17, 2025  
**Impact**: Product names now display correctly in payment history  
**Breaking Changes**: None (API response structure unchanged)
