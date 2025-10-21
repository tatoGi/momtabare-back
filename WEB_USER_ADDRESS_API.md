# Web User Address Management API

## Overview
Complete backend API system for managing user addresses with map coordinates (latitude/longitude) for web users.

## Database Schema

### Table: `web_user_addresses`
```sql
CREATE TABLE web_user_addresses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    web_user_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,           -- Address name (e.g., "სახლი", "ოფისი")
    city VARCHAR(255) NOT NULL,           -- City name (e.g., "თბილისი")
    address VARCHAR(500) NOT NULL,        -- Detailed address
    lat DECIMAL(10,7) NOT NULL,           -- Latitude
    lng DECIMAL(10,7) NOT NULL,           -- Longitude
    is_default TINYINT(1) DEFAULT 0,      -- Is this the default address?
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (web_user_id) REFERENCES web_users(id) ON DELETE CASCADE,
    INDEX (web_user_id),
    INDEX (lat, lng)
);
```

## API Endpoints

All endpoints require authentication via Sanctum token.

### 1. Get All User Addresses
**GET** `/api/users/{userId}/addresses`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "web_user_id": 5,
      "name": "სახლი",
      "city": "თბილისი",
      "address": "წერეთლის გამზირი 60",
      "lat": 41.7151,
      "lng": 44.8271,
      "is_default": true,
      "created_at": "2025-10-20T20:00:00.000000Z",
      "updated_at": "2025-10-20T20:00:00.000000Z"
    }
  ]
}
```

### 2. Create New Address
**POST** `/api/users/{userId}/addresses`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "ოფისი",
  "city": "თბილისი",
  "address": "რუსთაველის გამზირი 12",
  "lat": 41.6938,
  "lng": 44.8015
}
```

**Validation Rules:**
- `name`: required, string, max 255 characters
- `city`: required, string, max 255 characters
- `address`: required, string, max 500 characters
- `lat`: required, numeric, between -90 and 90
- `lng`: required, numeric, between -180 and 180

**Response (201):**
```json
{
  "success": true,
  "message": "Address created successfully",
  "data": {
    "id": 2,
    "web_user_id": 5,
    "name": "ოფისი",
    "city": "თბილისი",
    "address": "რუსთაველის გამზირი 12",
    "lat": 41.6938,
    "lng": 44.8015,
    "is_default": false,
    "created_at": "2025-10-20T20:05:00.000000Z",
    "updated_at": "2025-10-20T20:05:00.000000Z"
  }
}
```

**Note:** The first address created is automatically set as default.

### 3. Update Address
**PUT** `/api/users/{userId}/addresses/{addressId}`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body (all fields optional):**
```json
{
  "name": "სახლი (განახლებული)",
  "city": "თბილისი",
  "address": "წერეთლის გამზირი 62",
  "lat": 41.7152,
  "lng": 44.8272,
  "is_default": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "Address updated successfully",
  "data": {
    "id": 1,
    "web_user_id": 5,
    "name": "სახლი (განახლებული)",
    "city": "თბილისი",
    "address": "წერეთლის გამზირი 62",
    "lat": 41.7152,
    "lng": 44.8272,
    "is_default": true,
    "created_at": "2025-10-20T20:00:00.000000Z",
    "updated_at": "2025-10-20T20:10:00.000000Z"
  }
}
```

**Note:** Setting `is_default: true` automatically unsets all other addresses as default.

### 4. Delete Address
**DELETE** `/api/users/{userId}/addresses/{addressId}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Address deleted successfully"
}
```

**Note:** If the deleted address was the default, the most recently created address will be set as the new default.

### 5. Set Address as Default
**POST** `/api/users/{userId}/addresses/{addressId}/set-default`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Default address updated",
  "data": {
    "id": 2,
    "web_user_id": 5,
    "name": "ოფისი",
    "city": "თბილისი",
    "address": "რუსთაველის გამზირი 12",
    "lat": 41.6938,
    "lng": 44.8015,
    "is_default": true,
    "created_at": "2025-10-20T20:05:00.000000Z",
    "updated_at": "2025-10-20T20:15:00.000000Z"
  }
}
```

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Unauthorized access"
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Address not found"
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "lat": ["The lat must be between -90 and 90."]
  }
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "Failed to create address"
}
```

## Security Features

1. **Authentication Required**: All endpoints require a valid Sanctum token
2. **Authorization Check**: Users can only access/modify their own addresses
3. **User ID Validation**: The authenticated user ID must match the `userId` in the URL
4. **Foreign Key Constraint**: Addresses are automatically deleted when the user is deleted (CASCADE)

## Features

### Auto Default Assignment
- When a user creates their first address, it's automatically set as default
- First address has `is_default = true`

### Default Management
- Only one address can be default at a time
- Setting a new default automatically unsets the previous one
- When default address is deleted, the most recent address becomes default

### Data Ordering
- Addresses are returned with default addresses first
- Secondary sort by creation date (newest first)

## Frontend Integration

### TypeScript Interface
```typescript
export interface UserAddress {
  id: number
  web_user_id: number
  name: string
  city: string
  address: string
  lat: number
  lng: number
  is_default: boolean
  created_at: string
  updated_at: string
}

export interface AddressData {
  name: string
  city: string
  address: string
  lat: number
  lng: number
}
```

### API Service Example
```typescript
import axios from 'axios'

const API_BASE = 'http://your-api.com/api'

export const getUserAddresses = async (userId: number) => {
  const response = await axios.get(
    `${API_BASE}/users/${userId}/addresses`,
    {
      headers: {
        Authorization: `Bearer ${token}`
      }
    }
  )
  return response.data
}

export const createUserAddress = async (
  userId: number, 
  data: AddressData
) => {
  const response = await axios.post(
    `${API_BASE}/users/${userId}/addresses`,
    data,
    {
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    }
  )
  return response.data
}

export const deleteUserAddress = async (
  userId: number,
  addressId: number
) => {
  const response = await axios.delete(
    `${API_BASE}/users/${userId}/addresses/${addressId}`,
    {
      headers: {
        Authorization: `Bearer ${token}`
      }
    }
  )
  return response.data
}
```

## Testing Examples

### Create Address with cURL
```bash
curl -X POST http://localhost:8000/api/users/5/addresses \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "სახლი",
    "city": "თბილისი",
    "address": "წერეთლის გამზირი 60",
    "lat": 41.7151,
    "lng": 44.8271
  }'
```

### Get All Addresses with cURL
```bash
curl -X GET http://localhost:8000/api/users/5/addresses \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Delete Address with cURL
```bash
curl -X DELETE http://localhost:8000/api/users/5/addresses/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Database Indexes

Optimized for common queries:
- **web_user_id**: Fast lookup of all addresses for a user
- **lat, lng**: Geospatial queries (future feature: find addresses near location)

## Model Relationships

### WebUser Model
```php
public function addresses()
{
    return $this->hasMany(WebUserAddress::class, 'web_user_id');
}
```

### WebUserAddress Model
```php
public function webUser()
{
    return $this->belongsTo(WebUser::class);
}
```

## Future Enhancements

- [ ] Address geocoding (convert address string to coordinates automatically)
- [ ] Nearby address search using coordinates
- [ ] Address validation against real map data
- [ ] Address history/audit log
- [ ] Delivery zone validation
- [ ] Multiple address types (home, work, other)
