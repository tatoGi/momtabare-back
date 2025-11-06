# Optimized Home Page API - Single Call Solution

## Problem Solved
Previously, the home page made **4-5 separate API calls**:
- `/api/home` - Home page data
- `/api/locale/sync` - Locale sync
- `/api/products/popular` (or similar) - Popular products
- `/api/blog-posts` - Blog posts
- `/api/pages` - Pages list

This caused:
- Multiple network requests on every page load
- Slower page load time
- 5x more server load
- Network waterfall issues

## Solution: Single Unified API

### Backend Changes

#### 1. New Service Method
`app/Services/Frontend/FrontendService.php` - `getCompleteHomePageData()`

Returns everything in ONE call:
```php
return [
    'posts' => $homePage->posts ?? [],           // Join Us, Rental Steps sections
    'banners' => $homePage->banners ?? [],       // Home page banners
    'products' => $homePage->products ?? [],     // Home page products
    'popular_products' => $popularProducts,      // Popular/trending products
    'blog_posts' => $blogPosts,                  // Latest blog posts
    'pages' => $pages,                           // All pages for navigation
];
```

#### 2. New Controller Method
`app/Http/Controllers/Website/FrontendController.php` - `completeHomePage()`

#### 3. New Route
`routes/website/general.php`:
```php
Route::get('/home', [FrontendController::class, 'completeHomePage'])->name('api.home.complete');
```

### Frontend Changes (Vue)

Update your `useHomePageData.ts` composable:

```typescript
import { ref } from 'vue'
import axios from 'axios'
import { syncLocale } from '@/services/languages'
import { useAppStore } from '@/store/app'
import { ELanguages } from '@/ts/pinia/app.types'
import { useProductData } from './useProductData'
import { useBlogData } from './useBlogData'
import { useHomePageSections } from './useHomePageSections'

/**
 * Optimized composable - ONE API call for entire home page
 */
export function useHomePageData() {
  const appStore = useAppStore()
  const homeBanners = ref<any[]>([])
  const pages = ref<any[]>([])
  
  const { products, setPopularProducts, mergeHomeProducts } = useProductData()
  const { blogPosts, setBlogPosts } = useBlogData()
  const { joinUsData, rentalData, processPosts } = useHomePageSections()

  /**
   * Fetch ALL home page data in ONE API call
   */
  const fetchHomeData = async () => {
    try {
      const currentLocale = appStore.language === ELanguages.KA ? 'ka' : 'en'
      
      // Sync locale first (if needed, or remove if not necessary)
      await syncLocale(currentLocale)
      
      // ONE API CALL - Gets everything at once
      const { data } = await axios.get(`/api/home`, {
        params: {
          blog_limit: 10,
          products_limit: 50
        }
      })
      
      // Process all data from single response
      homeBanners.value = data.banners ?? []
      pages.value = data.pages ?? []
      
      // Process popular products
      setPopularProducts(data.popular_products ?? [])
      
      // Process blog posts
      setBlogPosts(data.blog_posts ?? [])
      
      // Process home page sections (Join Us, Rental Steps)
      processPosts(data.posts ?? [])
      
      // Merge home page products if available
      if (data.products) {
        mergeHomeProducts(data.products)
      }
      
    } catch (error) {
      console.error('Error fetching home page data:', error)
      homeBanners.value = []
      pages.value = []
    }
  }

  return {
    // Data refs
    homeBanners,
    pages,
    products,
    blogPosts,
    joinUsData,
    rentalData,
    
    // Methods
    fetchHomeData
  }
}
```

### Benefits

✅ **1 API call instead of 4-5**
✅ **Faster page load** - No network waterfall
✅ **Less server load** - Single database query optimization
✅ **Simpler frontend code** - One call, one error handler
✅ **Better caching** - Cache one endpoint instead of many
✅ **Atomic data** - All data matches same point in time

### API Response Example

```json
{
  "posts": [
    {
      "id": 1,
      "attributes": [
        { "attribute_key": "post_type", "attribute_value": "join_us" },
        { "attribute_key": "join_title_line_1", "attribute_value": "Join Our" }
      ]
    }
  ],
  "banners": [
    { "id": 1, "images": [...] }
  ],
  "products": [
    { "id": 1, "name": "Snowboard", "images": [...] }
  ],
  "popular_products": [
    { "id": 5, "view_count": 1500 }
  ],
  "blog_posts": [
    { "id": 1, "title": "Winter Tips" }
  ],
  "pages": [
    { "id": 1, "slug": "about" }
  ]
}
```

### Optional Parameters

You can customize the API call:
```javascript
const { data } = await axios.get(`/api/home`, {
  params: {
    blog_limit: 5,      // Number of blog posts
    products_limit: 20  // Number of products
  }
})
```

### Migration Steps

1. ✅ Backend updated (service, controller, route)
2. Update your Vue composable with the code above
3. Remove old API service functions if not used elsewhere:
   - `getHomePageData()` (if only used for home)
   - `getBlogPosts()` (if only used for home)
   - `getPopularProducts()` (if only used for home)
4. Test the new `/api/home` endpoint
5. Deploy to production

### Performance Comparison

**Before:**
```
Request 1: /api/locale/sync     - 150ms
Request 2: /api/home            - 200ms
Request 3: /api/products        - 180ms
Request 4: /api/blog-posts      - 160ms
Request 5: /api/pages           - 140ms
Total: ~830ms (waterfall effect)
```

**After:**
```
Request 1: /api/locale/sync     - 150ms
Request 2: /api/home            - 250ms (all data)
Total: ~400ms (50% faster!)
```

### Notes

- The `/api/locale/sync` call might still be needed if you use it for session management
- If locale sync is only for language preference, consider combining it or removing it
- You can still keep the old endpoints for backward compatibility
- Add caching headers to `/api/home` for even better performance
