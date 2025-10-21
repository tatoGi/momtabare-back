# Facebook OAuth Authentication - Setup Guide

## What's Been Configured

### 1. Package Installation
- ✅ Laravel Socialite installed (`composer require laravel/socialite`)

### 2. Database Changes
- ✅ `facebook_id` column added to `web_users` table
- ✅ Column is nullable, unique, indexed
- ✅ `avatar` column already existed

### 3. Configuration Files

#### `.env` File
Added Facebook OAuth credentials:
```env
FACEBOOK_CLIENT_ID=792836493731133
FACEBOOK_CLIENT_SECRET=your_facebook_app_secret_here
FACEBOOK_REDIRECT_URI="${APP_URL}/auth/facebook/callback"
```

#### `config/services.php`
Added Facebook provider configuration:
```php
'facebook' => [
    'client_id' => env('FACEBOOK_CLIENT_ID'),
    'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    'redirect' => env('FACEBOOK_REDIRECT_URI'),
],
```

### 4. Model Updates
- ✅ `facebook_id` added to `WebUser` model's `$fillable` array

### 5. Controller Created
- ✅ `App\Http\Controllers\Auth\SocialAuthController`
  - `redirectToFacebook()` - Initiates OAuth flow
  - `handleFacebookCallback()` - Handles Facebook callback
  - Auto-creates users or links existing accounts

### 6. Routes Added
```php
// In routes/website/auth.php
Route::get('/auth/facebook', [SocialAuthController::class, 'redirectToFacebook']);
Route::get('/auth/facebook/callback', [SocialAuthController::class, 'handleFacebookCallback']);
```

## What You Need to Do Next

### 1. Get Your Facebook App Secret

1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Navigate to your app (ID: 792836493731133)
3. Go to **Settings > Basic**
4. Copy your **App Secret**
5. Update `.env`:
   ```env
   FACEBOOK_CLIENT_SECRET=your_actual_facebook_app_secret
   ```

### 2. Configure Facebook App Settings

In your Facebook App dashboard:

1. **Add Platform** (if not done):
   - Go to **Settings > Basic**
   - Click **Add Platform** → Choose **Website**
   - Add your website URL: `http://localhost` (for local) or your production URL

2. **Configure OAuth Redirect URIs**:
   - Go to **Facebook Login > Settings**
   - Add Valid OAuth Redirect URIs:
     ```
     http://localhost/auth/facebook/callback
     http://localhost:8000/auth/facebook/callback
     http://127.0.0.1:5173/auth/facebook/callback
     https://yourdomain.com/auth/facebook/callback
     ```

3. **Enable Facebook Login**:
   - Go to **Products** in left sidebar
   - Add **Facebook Login** product if not already added
   - Configure settings as needed

4. **Set Required Permissions**:
   - Go to **App Review > Permissions and Features**
   - Request these permissions:
     - `email` (usually auto-approved)
     - `public_profile` (default)

### 3. Test the Implementation

#### API Endpoints Available:

**Initiate Facebook Login:**
```
GET /api/auth/facebook
```
This redirects users to Facebook OAuth page.

**Callback URL (handled automatically):**
```
GET /api/auth/facebook/callback
```
Facebook redirects here after user authorizes.

#### Response Format:

**Success (Existing User):**
```json
{
    "success": true,
    "message": "Login successful",
    "user": {
        "id": 1,
        "first_name": "John",
        "surname": "Doe",
        "email": "john@example.com",
        "facebook_id": "123456789",
        "avatar": "https://graph.facebook.com/123456789/picture",
        ...
    },
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz..."
}
```

**Success (New User):**
```json
{
    "success": true,
    "message": "Registration successful",
    "user": { ... },
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz..."
}
```

**Error:**
```json
{
    "success": false,
    "message": "Facebook authentication failed",
    "error": "Error details..."
}
```

### 4. Frontend Integration

#### React/Vue Example:

```javascript
// Initiate Facebook login
function loginWithFacebook() {
    window.location.href = 'http://localhost:8000/api/auth/facebook';
}

// Handle callback (create a callback page)
// Parse URL parameters and extract token
const urlParams = new URLSearchParams(window.location.search);
const token = urlParams.get('token');
if (token) {
    localStorage.setItem('auth_token', token);
    // Redirect to dashboard
}
```

#### Better Approach - Popup Window:

```javascript
function loginWithFacebookPopup() {
    const width = 600;
    const height = 700;
    const left = (screen.width / 2) - (width / 2);
    const top = (screen.height / 2) - (height / 2);
    
    const popup = window.open(
        'http://localhost:8000/api/auth/facebook',
        'facebook-login',
        `width=${width},height=${height},top=${top},left=${left}`
    );
    
    // Listen for callback message
    window.addEventListener('message', (event) => {
        if (event.data.token) {
            localStorage.setItem('auth_token', event.data.token);
            popup.close();
            // Redirect or update UI
        }
    });
}
```

### 5. How It Works

1. **User clicks "Login with Facebook"** → Frontend sends user to `/api/auth/facebook`
2. **Laravel redirects to Facebook** → User sees Facebook OAuth consent screen
3. **User authorizes** → Facebook redirects to `/api/auth/facebook/callback`
4. **Backend processes callback:**
   - Fetches user data from Facebook
   - Checks if user exists by `facebook_id`
   - If yes: Log in user
   - If no: Check by email
     - If email exists: Link Facebook account
     - If email doesn't exist: Create new user
5. **Return JWT token** → Frontend stores token and authenticates requests

### 6. Security Considerations

- ✅ Password is auto-generated for OAuth users (they can't login with password)
- ✅ Email is marked as verified (trusted from Facebook)
- ✅ User is auto-activated (`is_active = true`)
- ✅ Token is returned for API authentication
- ✅ Uses Sanctum for token-based auth

### 7. Testing Checklist

- [ ] Add Facebook App Secret to `.env`
- [ ] Configure Facebook app redirect URIs
- [ ] Test redirect to Facebook OAuth page
- [ ] Test successful login with existing email
- [ ] Test registration with new Facebook account
- [ ] Test linking Facebook to existing account
- [ ] Verify token works for authenticated API calls
- [ ] Test avatar download from Facebook
- [ ] Test error handling (denied permission, etc.)

## Troubleshooting

### Common Issues:

1. **"Invalid OAuth redirect URI"**
   - Make sure your callback URL is added in Facebook App settings
   - Check that the domain matches exactly

2. **"App Not Set Up"**
   - Your Facebook app might be in development mode
   - Add your Facebook account as a test user
   - Or switch app to Live mode (requires business verification)

3. **"stateless() method not found" (lint warning)**
   - This is just a lint warning, ignore it
   - The method exists in Laravel Socialite for API usage

4. **CORS errors**
   - Make sure your frontend domain is in `SANCTUM_STATEFUL_DOMAINS`
   - Check CORS configuration in `config/cors.php`

## Production Deployment

Before going live:

1. ✅ Switch Facebook app from Development to Live mode
2. ✅ Update `FACEBOOK_REDIRECT_URI` in production `.env`
3. ✅ Add production domain to Facebook app settings
4. ✅ Enable HTTPS (required by Facebook)
5. ✅ Request additional Facebook permissions if needed
6. ✅ Test thoroughly in production environment

## API Documentation

Add this to your API documentation:

```markdown
### Facebook OAuth Login

**Endpoint:** `GET /api/auth/facebook`
**Description:** Redirects user to Facebook OAuth page
**Authentication:** Not required

**Callback:** `GET /api/auth/facebook/callback`
**Description:** Handles Facebook OAuth callback (automatic)
**Returns:** JSON with user data and auth token
```

---

That's it! Your Facebook OAuth authentication is ready to use. Just add your App Secret and configure the Facebook app settings.
