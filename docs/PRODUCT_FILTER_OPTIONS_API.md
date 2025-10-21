# Product Filter Options API

## Overview
A new API endpoint has been created to provide all available filter options for the products page. This eliminates the need to fetch all products on the frontend just to extract filter options.

## API Endpoint

### Get Product Filter Options
**Endpoint:** `GET /api/products/filters/options`

**Description:** Returns all available filter options including brands, colors, price ranges, and categories with their product counts.

**Authentication:** Not required (public endpoint)

**Response Format:**
```json
{
  "brands": [
    {
      "key": "the-north-face",
      "translations": {
        "ka": "ნორთ ფეისი",
        "en": "The North Face"
      },
      "count": 25
    },
    {
      "key": "salomon",
      "translations": {
        "ka": "სალომონი",
        "en": "Salomon"
      },
      "count": 18
    }
  ],
  "colors": [
    {
      "key": "red",
      "translations": {
        "ka": "წითელი",
        "en": "Red"
      },
      "count": 15
    },
    {
      "key": "blue",
      "translations": {
        "ka": "ლურჯი",
        "en": "Blue"
      },
      "count": 12
    }
  ],
  "price_range": {
    "min": 10,
    "max": 500
  },
  "categories": [
    {
      "id": 1,
      "slug": "winter-sport",
      "name": "Winter Sport",
      "translations": {
        "ka": "ზამთრის სპორტი",
        "en": "Winter Sport"
      },
      "children": [
        {
          "id": 2,
          "slug": "skiing",
          "name": "Skiing",
          "translations": {
            "ka": "სათხილამურო",
            "en": "Skiing"
          }
        }
      ]
    }
  ],
  "total_products": 156
}
```

## Frontend Integration

### TypeScript Types
```typescript
// types/productFilters.types.ts
export interface IFilterTranslations {
  ka: string
  en: string
}

export interface IFilterBrand {
  key: string
  translations: IFilterTranslations
  count: number
}

export interface IFilterColor {
  key: string
  translations: IFilterTranslations
  count: number
}

export interface IPriceRange {
  min: number
  max: number
}

export interface IFilterCategory {
  id: number
  slug: string
  name: string
  translations: IFilterTranslations
  children?: IFilterCategory[]
}

export interface IProductFilterOptions {
  brands: IFilterBrand[]
  colors: IFilterColor[]
  price_range: IPriceRange
  categories: IFilterCategory[]
  total_products: number
}
```

### Service Function
```typescript
// services/products.ts
import axios from 'axios'
import { IProductFilterOptions } from '@/types/productFilters.types'

export async function getProductFilterOptions(): Promise<IProductFilterOptions> {
  const response = await axios.get('/api/products/filters/options')
  return response.data
}
```

### Vue Component Usage
```typescript
// In your Products.vue component

import { getProductFilterOptions } from '@/services/products'
import type { IProductFilterOptions } from '@/types/productFilters.types'

const filterOptions = ref<IProductFilterOptions | null>(null)

// Fetch filter options on component mount
async function loadFilterOptions() {
  try {
    filterOptions.value = await getProductFilterOptions()
    
    // Update your local state with the fetched data
    availableBrands.value = filterOptions.value.brands.map(brand => ({
      name: brand.translations[computedLanguage.value] || brand.translations.ka,
      key: brand.key,
      count: brand.count,
      translations: brand.translations
    }))
    
    availableColors.value = filterOptions.value.colors.map(color => ({
      name: color.translations[computedLanguage.value] || color.translations.ka,
      key: color.key,
      count: color.count,
      translations: color.translations,
      class: getColorClass(color.key)
    }))
    
    // Set price range
    minPrice.value = filterOptions.value.price_range.min
    maxPrice.value = filterOptions.value.price_range.max
    
  } catch (error) {
    console.error('Error loading filter options:', error)
  }
}

// Call in your initializeComponent function
async function initializeComponent() {
  initializeFiltersFromRoute()
  
  if (!categoryStore.getCategories || categoryStore.getCategories.length === 0) {
    await categoryStore.fetchCategories()
  }
  
  // Use the new API instead of extracting from products
  await loadFilterOptions()
  
  await fetchProducts()
}
```

## Benefits

1. **Performance**: Single API call instead of fetching all products and processing them on the frontend
2. **Accuracy**: Always shows current available filter options based on active products
3. **Localization**: Built-in support for Georgian and English translations
4. **Product Counts**: Shows how many products match each filter option
5. **Sorted Results**: Brands and colors are sorted by popularity (count)

## Caching Recommendation

Consider caching this endpoint for 5-15 minutes since filter options don't change frequently:

```php
// In FrontendController.php
use Illuminate\Support\Facades\Cache;

public function productFilterOptions(Request $request)
{
    return Cache::remember('product_filter_options', 600, function () {
        // ... existing code
    });
}
```

## Testing

Test the endpoint:
```bash
curl http://your-domain.com/api/products/filters/options
```

Or in your browser:
```
http://your-domain.com/api/products/filters/options
```
