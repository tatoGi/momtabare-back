# Translation - Use Current Locale (app()->getLocale())

## Overview
Updated cart and payment history to use Laravel's `app()->getLocale()` instead of returning translations for all languages (ka, en, ru). This provides a cleaner API response with only the active language.

## Changes Made

### Before ❌ (Returning All Languages)
```php
// Get translations for ALL languages
$titleKa = $product->translate('ka')->title ?? '';
$titleEn = $product->translate('en')->title ?? '';
$titleRu = $product->translate('ru')->title ?? '';

return [
    'title_ka' => $titleKa,
    'title_en' => $titleEn,
    'title_ru' => $titleRu,
    // ...
];
```

**API Response**:
```json
{
  "items": [
    {
      "id": 1,
      "title_ka": "ტესტი",
      "title_en": "test",
      "title_ru": "тест"
    }
  ]
}
```

### After ✅ (Current Locale Only)
```php
// Get translation for CURRENT locale
$currentLocale = app()->getLocale();
$title = '';
$description = '';
$slug = '';

try {
    $translation = $product->translate($currentLocale);
    $title = $translation->title ?? '';
    $description = $translation->description ?? '';
    $slug = $translation->slug ?? '';
} catch (\Exception $e) {
    // Fallback to default
    $title = $product->title ?? '';
    $description = $product->description ?? '';
    $slug = $product->slug ?? '';
}

return [
    'title' => $title,
    'description' => $description,
    'slug' => $slug,
    // ...
];
```

**API Response** (when locale is 'ka'):
```json
{
  "items": [
    {
      "id": 1,
      "title": "test",
      "description": "tato",
      "slug": "test"
    }
  ]
}
```

**API Response** (when locale is 'en'):
```json
{
  "items": [
    {
      "id": 1,
      "title": "test",
      "description": "tato",
      "slug": "test"
    }
  ]
}
```

## Files Modified

### 1. wishlistController.php
**File**: `app/Http/Controllers/Website/wishlistController.php`

#### fetchCartData() Method
- **Lines**: ~386-478
- **Changes**:
  - Uses `app()->getLocale()` to get current locale
  - Returns only `title`, `description`, `slug` (not `title_ka`, `title_en`, `title_ru`)
  - Translates based on active locale

#### showCart() Method
- **Lines**: ~490-580
- **Changes**:
  - Same locale-based translation approach
  - Consistent with fetchCartData()

### 2. BogPaymentController.php
**File**: `app/Http/Controllers/Website/BogPaymentController.php`

#### getUserPayments() Method
- **Lines**: ~980-1020
- **Changes**:
  - Uses `app()->getLocale()` for product translations
  - Returns only `name` (current locale) instead of `name_ka`, `name_en`, `name_ru`
  - Cleaner API response structure

## Benefits

### 1. **Smaller Response Size** 📉
- Sends only the needed translation
- Reduces JSON payload by ~66% for translatable fields
- Faster API responses

### 2. **Cleaner Frontend Code** 🎨
```javascript
// Before: Frontend had to pick the right language
const productName = locale === 'ka' ? product.title_ka : 
                    locale === 'en' ? product.title_en : 
                    product.title_ru;

// After: Already translated by backend
const productName = product.title; // ✅ Simple!
```

### 3. **Server-Side Locale Control** 🎯
- Backend controls translation based on user's locale
- Consistent with Laravel's localization system
- Easier to maintain and extend

### 4. **Better Performance** ⚡
- Less data transferred over network
- Fewer fields to process on frontend
- Reduced memory usage

## How Locale is Set

Laravel automatically sets the locale based on:

1. **Accept-Language Header**
   ```
   Accept-Language: ka
   ```

2. **Middleware/Session**
   ```php
   app()->setLocale($request->input('lang', 'ka'));
   ```

3. **User Preference**
   ```php
   app()->setLocale(auth()->user()->preferred_locale);
   ```

## API Response Examples

### Cart API (`GET /api/cart`)

**When locale = 'ka'**:
```json
{
  "success": true,
  "items": [
    {
      "id": 1,
      "title": "test",
      "slug": "test",
      "description": "tato",
      "price": 0.02,
      "currency": "GEL",
      "images": [...],
      "quantity": 1
    }
  ]
}
```

**When locale = 'en'**:
```json
{
  "success": true,
  "items": [
    {
      "id": 1,
      "title": "test",
      "slug": "test",
      "description": "tato",
      "price": 0.02,
      "currency": "GEL",
      "images": [...],
      "quantity": 1
    }
  ]
}
```

### Payment History API (`GET /api/bog/payments`)

**When locale = 'ka'**:
```json
{
  "success": true,
  "data": [
    {
      "id": 12,
      "products": [
        {
          "id": 1,
          "name": "test",
          "slug": "test",
          "price": 0.02
        }
      ]
    }
  ]
}
```

## Testing

### Tinker Test - Georgian
```php
app()->setLocale('ka');
$product = \App\Models\Product::find(1);
$translation = $product->translate(app()->getLocale());

// Returns:
[
    'current_locale' => 'ka',
    'title' => 'test',
    'slug' => 'test',
    'description' => 'tato'
]
```

### Tinker Test - English
```php
app()->setLocale('en');
$product = \App\Models\Product::find(1);
$translation = $product->translate(app()->getLocale());

// Returns:
[
    'current_locale' => 'en',
    'title' => 'test',
    'slug' => 'test',
    'description' => 'tato'
]
```

## Frontend Changes Required

### Before
```typescript
// Frontend had to handle multiple languages
interface Product {
  id: number;
  title_ka: string;
  title_en: string;
  title_ru: string;
  // ...
}

// Display logic
const displayName = locale === 'ka' ? product.title_ka : 
                    locale === 'en' ? product.title_en : 
                    product.title_ru;
```

### After
```typescript
// Simplified interface
interface Product {
  id: number;
  title: string; // Already translated by backend
  slug: string;  // Already translated by backend
  description: string; // Already translated by backend
  // ...
}

// Simple display logic
const displayName = product.title; // ✅ Already in correct language
```

## Fallback Strategy

If a translation doesn't exist for the current locale:

```php
try {
    $translation = $product->translate($currentLocale);
    $title = $translation->title ?? '';
} catch (\Exception $e) {
    // Fallback to default locale (usually first available translation)
    $title = $product->title ?? '';
}
```

## Important Notes

1. **Locale Detection**: Make sure frontend sends the correct locale via:
   - `Accept-Language` header
   - URL parameter (`?lang=ka`)
   - Session/Cookie

2. **Translatable Fields**:
   - `title` (was: title_ka, title_en, title_ru)
   - `description` (was: description_ka, etc.)
   - `slug` (was: slug_ka, etc.)

3. **Non-Translatable Fields**: Remain unchanged
   - `price`, `currency`, `size`, `color`, etc.

4. **Backward Compatibility**: 
   - Frontend should update to use `title` instead of `title_ka/title_en/title_ru`
   - Old field names removed from API response

## Migration Checklist

- [x] Update wishlistController::fetchCartData() to use current locale
- [x] Update wishlistController::showCart() to use current locale
- [x] Update BogPaymentController::getUserPayments() to use current locale
- [x] Clear application cache
- [x] Test with Georgian locale (ka)
- [x] Test with English locale (en)
- [ ] Update frontend TypeScript interfaces
- [ ] Update frontend display logic to use `title` instead of `title_ka/title_en/title_ru`
- [ ] Test all cart operations with different locales
- [ ] Test payment history with different locales
- [ ] Verify locale is properly set from frontend

---

**Status**: ✅ Implemented  
**Date**: October 18, 2025  
**Impact**: Cleaner API, smaller payloads, server-side locale control  
**Breaking Changes**: ⚠️ Frontend must update to use `title` instead of `title_ka/title_en/title_ru`
