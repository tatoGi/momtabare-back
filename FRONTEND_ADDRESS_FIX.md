# Frontend Integration Guide for Address API

## Issue Diagnosis

The authentication error occurs because requests are being sent to the **Vite dev server** (`http://localhost:5173`) instead of the **Laravel backend** (`http://127.0.0.1:8000`).

### Evidence from Logs
```
❌ addresses.ts:89 XHR finished loading: POST "http://localhost:5173/api/users/45/addresses"
✅ user.ts:60 XHR finished loading: GET "http://127.0.0.1:8000/api/me"
```

Notice that `/api/me` works because it goes to `127.0.0.1:8000`, but `/api/users/45/addresses` fails because it goes to `localhost:5173`.

## Solution 1: Fix API Base URL in Frontend Service

### Current (Broken)
```typescript
// addresses.ts
const API_BASE = '/api' // ❌ This resolves to localhost:5173/api
```

### Fixed Option A: Use Full Backend URL
```typescript
// addresses.ts
const API_BASE = 'http://127.0.0.1:8000/api' // ✅ Direct to Laravel backend
```

### Fixed Option B: Use Environment Variable
```typescript
// addresses.ts
const API_BASE = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'
```

Then in your `.env` file:
```env
VITE_API_URL=http://127.0.0.1:8000/api
```

## Solution 2: Fix Vite Proxy Configuration

If you want to keep using relative URLs (`/api`), ensure your `vite.config.ts` has proper proxy setup:

```typescript
// vite.config.ts
export default defineConfig({
  server: {
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
        secure: false,
        // Don't rewrite the path - keep /api prefix
      }
    }
  }
})
```

## Solution 3: Use Axios Instance with Correct Base URL

Create a centralized axios instance:

```typescript
// src/services/api.ts
import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api',
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
})

// Add token to all requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token') // or however you store it
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export default api
```

Then use it in your service:

```typescript
// src/services/addresses.ts
import api from './api'

export const getUserAddresses = async (userId: number) => {
  const response = await api.get(`/users/${userId}/addresses`)
  return response.data
}

export const createUserAddress = async (
  userId: number,
  data: AddressData
) => {
  const response = await api.post(`/users/${userId}/addresses`, data)
  return response.data
}
```

## Testing the Fix

### 1. Test Authentication Endpoint
First, verify authentication works:

```bash
curl -X GET http://127.0.0.1:8000/api/test-address-auth \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

Expected response:
```json
{
  "authenticated": true,
  "user": {
    "id": 45,
    "email": "tato.laperashvili95@gmail.com"
  },
  "bearer_token_present": true
}
```

### 2. Test Address Creation
```bash
curl -X POST http://127.0.0.1:8000/api/users/45/addresses \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "სახლი",
    "city": "თბილისი",
    "address": "ნაძალადევი, წიკლაური 44",
    "lat": 41.7151,
    "lng": 44.8271
  }'
```

Expected response:
```json
{
  "success": true,
  "message": "Address created successfully",
  "data": {
    "id": 1,
    "web_user_id": 45,
    "name": "სახლი",
    "city": "თბილისი",
    "address": "ნაძალადევი, წიკლაური 44",
    "lat": 41.7151,
    "lng": 44.8271,
    "is_default": true,
    "created_at": "2025-10-20T20:00:00.000000Z",
    "updated_at": "2025-10-20T20:00:00.000000Z"
  }
}
```

## Common Issues & Solutions

### Issue 1: "Not authenticated" error
**Cause**: Token not being sent or sent incorrectly

**Solution**: 
- Ensure `Authorization: Bearer {token}` header is present
- Check token is valid: `php artisan tinker` then `\Laravel\Sanctum\PersonalAccessToken::find(53)`
- Verify token belongs to user 45

### Issue 2: "Unauthorized access" (403)
**Cause**: User ID in URL doesn't match authenticated user

**Solution**:
- URL should be `/api/users/45/addresses` for user with ID 45
- Authenticated token must belong to user 45
- Check: `GET /api/me` to see which user is authenticated

### Issue 3: CORS errors
**Cause**: Frontend origin not allowed

**Solution**: Already configured! `http://localhost:5173` is in allowed origins

### Issue 4: Getting HTML instead of JSON
**Cause**: Request not reaching API (proxy issue) or Laravel returning redirect

**Solution**:
- Check request actually goes to `127.0.0.1:8000`, not `localhost:5173`
- Verify no middleware redirecting to login page
- Check Laravel logs: `tail -f storage/logs/laravel.log`

## Backend Changes Made

1. ✅ Removed `middleware(['auth:sanctum'])` from routes (to match other working endpoints)
2. ✅ Added detailed authentication logging in controller
3. ✅ Added test endpoint `/api/test-address-auth` for debugging
4. ✅ Separated 401 (not authenticated) from 403 (unauthorized) errors
5. ✅ Routes cleared and recached

## Next Steps for Frontend Team

1. **Immediate Fix**: Update `addresses.ts` service to use correct base URL
   ```typescript
   const API_BASE = 'http://127.0.0.1:8000/api'
   ```

2. **Long-term Fix**: Implement centralized API service with axios instance

3. **Verify**: Test the `/api/test-address-auth` endpoint first to confirm auth works

4. **Check Logs**: If still failing, check Laravel logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```
   Look for the "Address creation attempt" log entry with full headers

## Example Working Service File

```typescript
// src/services/addresses.ts
import axios from 'axios'

const API_BASE = 'http://127.0.0.1:8000/api'

// Get token from your auth store/localStorage
const getAuthHeader = () => {
  const token = localStorage.getItem('auth_token') // adjust as needed
  return token ? { Authorization: `Bearer ${token}` } : {}
}

export const getUserAddresses = async (userId: number) => {
  try {
    const response = await axios.get(
      `${API_BASE}/users/${userId}/addresses`,
      {
        headers: {
          ...getAuthHeader(),
          'Accept': 'application/json',
        },
        withCredentials: true,
      }
    )
    return response.data
  } catch (error) {
    console.error('Error fetching addresses:', error)
    throw error
  }
}

export const createUserAddress = async (
  userId: number,
  data: AddressData
) => {
  try {
    const response = await axios.post(
      `${API_BASE}/users/${userId}/addresses`,
      data,
      {
        headers: {
          ...getAuthHeader(),
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        withCredentials: true,
      }
    )
    return response.data
  } catch (error) {
    console.error('Error creating address:', error)
    throw error
  }
}

export const deleteUserAddress = async (
  userId: number,
  addressId: number
) => {
  try {
    const response = await axios.delete(
      `${API_BASE}/users/${userId}/addresses/${addressId}`,
      {
        headers: {
          ...getAuthHeader(),
          'Accept': 'application/json',
        },
        withCredentials: true,
      }
    )
    return response.data
  } catch (error) {
    console.error('Error deleting address:', error)
    throw error
  }
}
```

## Debug Checklist

- [ ] Request goes to `http://127.0.0.1:8000/api/...` not `http://localhost:5173/api/...`
- [ ] `Authorization: Bearer {token}` header is present
- [ ] Token is valid and belongs to the correct user
- [ ] `/api/test-address-auth` endpoint returns `authenticated: true`
- [ ] User ID in URL matches authenticated user's ID
- [ ] Check Laravel logs for "Address creation attempt" entry
- [ ] CORS headers are present in response
- [ ] No 302 redirects happening
