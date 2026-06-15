# MakemeupAI API Contracts (Draft)

## 1. Purpose

This document defines the initial API contract between frontend and backend for MakemeupAI.
It is a draft meant to guide implementation and can be versioned as backend evolves.

## 2. Conventions

- Base URL (dev): `http://localhost:8000/api` (frontend uses `VITE_API_URL` or defaults to localhost)
- Base URL (prod): `https://makemeupai-production.up.railway.app/api`
- Auth: **Laravel Sanctum** — CSRF (`GET /sanctum/csrf-cookie` + `GET /sanctum/csrf-token`) before mutating requests; login/register return `data.token`; frontend sends `Authorization: Bearer {token}` on all API requests (sessionStorage). Axios uses `withCredentials: true` for CSRF cookies.
- Content-Type: `application/json` (multipart for wardrobe/selfie uploads)
- Time format: ISO 8601 (UTC)
- IDs: numeric auto-increment IDs

> **Note:** Cross-origin production (Vercel SPA + Railway API) uses Bearer tokens from login/register because browser session cookies may not persist across sites. Local dev may still use cookies when same-origin.

## 3. Standard Response Shapes

### Success

```json
{
  "success": true,
  "message": "Optional success message",
  "data": {}
}
```

### Error

```json
{
  "success": false,
  "message": "Human-readable error message",
  "errors": {
    "fieldName": ["Validation error text"]
  }
}
```

### Pagination

```json
{
  "success": true,
  "data": [],
  "meta": {
    "page": 1,
    "perPage": 10,
    "total": 100,
    "totalPages": 10
  }
}
```

## 4. Auth Endpoints

## POST `/auth/register`

Create user account.

Request:

```json
{
  "name": "Ayesha Noor",
  "email": "ayesha@example.com",
  "password": "StrongPassword123!",
  "passwordConfirmation": "StrongPassword123!"
}
```

Response data:

```json
{
  "user": {
    "id": "u_123",
    "name": "Ayesha Noor",
    "email": "ayesha@example.com"
  },
  "token": "token_here"
}
```

## POST `/auth/login`

Request:

```json
{
  "email": "ayesha@example.com",
  "password": "StrongPassword123!"
}
```

Response data:

```json
{
  "user": {
    "id": "u_123",
    "name": "Ayesha Noor",
    "email": "ayesha@example.com"
  },
  "token": "token_here"
}
```

## POST `/auth/logout`

Auth required. Invalidates current session token.

## GET `/auth/me`

Returns current user profile.

## 5. User Profile Endpoints

## GET `/users/me`

Fetch logged-in user profile and preferences.

## PATCH `/users/me`

Update profile.

Request (partial allowed):

```json
{
  "name": "Updated Name",
  "phone": "+923001234567",
  "city": "Lahore"
}
```

## PATCH `/users/me/preferences`

Save user style preferences.

Request:

```json
{
  "favoriteStyles": ["minimal", "elegant"],
  "preferredColors": ["neutral", "pastel"],
  "budgetRange": "mid",
  "defaultEventType": "casual"
}
```

## 6. Wardrobe Endpoints

## GET `/wardrobe/items`

List wardrobe items (filterable).

Query params:

- `category`
- `color`
- `season`
- `search`
- `page`, `perPage`

## POST `/wardrobe/items`

Create wardrobe item.

Request:

```json
{
  "name": "Black Maxi Dress",
  "category": "dresses",
  "color": "black",
  "season": "all",
  "imageUrl": "https://cdn.example.com/items/dress.jpg"
}
```

## GET `/wardrobe/items/:itemId`

Get single item details.

## PATCH `/wardrobe/items/:itemId`

Update item fields.

## DELETE `/wardrobe/items/:itemId`

Delete item.

## POST `/wardrobe/items/:itemId/analyze`

Trigger AI tagging (clothing type/color/pattern extraction).

Response data:

```json
{
  "detectedCategory": "dresses",
  "detectedColors": ["black"],
  "detectedPattern": "solid",
  "confidence": 0.91
}
```

## 7. Beautician Endpoints

## GET `/beauticians`

List beauticians with filters.

Query params:

- `serviceType`
- `skillLevel` (`beginner|intermediate|expert`)
- `minPrice`, `maxPrice`
- `city`
- `availableDate`
- `page`, `perPage`

## GET `/beauticians/:beauticianId`

Fetch beautician profile, services, portfolio, and ratings.

## GET `/beauticians/:beauticianId/availability`

Get available slots.

Query params:

- `date` (required)

## POST `/beauticians/:beauticianId/verify-skill`

Admin/system endpoint to run skill verification on portfolio.

Response data:

```json
{
  "skillBadge": "expert",
  "qualityScore": 92,
  "originalityScore": 88,
  "verifiedAt": "2026-04-27T10:20:00Z"
}
```

## 8. Booking Endpoints

## POST `/bookings`

Create booking.

Request:

```json
{
  "beauticianId": "b_123",
  "serviceType": "bridal_makeup",
  "bookingDate": "2026-05-01",
  "bookingTime": "17:30",
  "lookPlanId": "look_456",
  "notes": "Need soft glam look"
}
```

Response data:

```json
{
  "bookingId": "bk_789",
  "status": "confirmed",
  "totalPrice": 8000
}
```

## GET `/bookings`

List user bookings.

Query params:

- `status` (`pending|confirmed|completed|cancelled`)
- `upcoming` (`true|false`)
- `page`, `perPage`

## GET `/bookings/:bookingId`

Fetch booking detail.

## PATCH `/bookings/:bookingId/cancel`

Cancel booking (based on policy).

## 9. AI Recommendation Endpoints

Auth: Sanctum required for all routes in this section.

**MVP note:** Face analysis uses deterministic heuristics (file hash + user id), not a real CV model. Response shapes are stable for future Azure Face or custom ML integration.

## GET `/ai/face-profile`

Returns stored selfie URL and cached traits.

Response data:

```json
{
  "profile_photo_url": "http://localhost:8000/storage/selfies/1.jpg",
  "face_traits": {
    "faceShape": "oval",
    "skinTone": "warm-medium",
    "hairLength": "medium",
    "analyzed_at": "2026-06-03T12:00:00+00:00"
  }
}
```

## POST `/ai/face-analysis`

Upload selfie (`multipart/form-data`, field `image`, max 5MB). Saves to user `profile_photo`, runs analysis, caches `face_traits`.

Response data:

```json
{
  "faceShape": "oval",
  "skinTone": "warm-medium",
  "hairLength": "medium",
  "profile_photo_url": "http://localhost:8000/storage/selfies/1.jpg",
  "analyzed_at": "2026-06-03T12:00:00+00:00"
}
```

## POST `/ai/look-recommendations`

Generate makeup + hairstyle + mehndi suggestions from stored `face_traits`. Returns 422 if user has not uploaded a selfie.

Request:

```json
{
  "eventType": "wedding",
  "styleMood": "elegant"
}
```

`eventType`: `wedding` | `party` | `casual` | `work` | `formal`

`styleMood`: `elegant` | `natural` | `bold` | `soft`

Response data:

```json
{
  "makeup": ["Soft Glam Warm", "Classic Bridal Glow"],
  "hairstyle": ["Textured Low Bun", "Half-Up Curls"],
  "mehndi": ["Minimal Arabic", "Floral Traditional"]
}
```

## POST `/ai/outfit-recommendations`

Generate outfit suggestions using wardrobe + context.

Request:

```json
{
  "eventType": "party",
  "weather": {
    "tempC": 29,
    "condition": "clear"
  },
  "mood": "trendy"
}
```

Response data:

```json
{
  "outfits": [
    {
      "items": ["Top #12", "Bottom #5", "Shoes #9"],
      "score": 0.93,
      "reason": "Event-appropriate and weather-friendly"
    }
  ]
}
```

## 10. Build Your Look Endpoints

## POST `/looks`

Save a generated/custom look.

Request:

```json
{
  "name": "Wedding Evening Look",
  "eventType": "wedding",
  "mood": "elegant",
  "makeup": "Soft Glam Warm",
  "hairstyle": "Textured Low Bun",
  "mehndi": "Minimal Arabic",
  "outfitItemIds": ["item_12", "item_5", "item_9"]
}
```

## GET `/looks`

List saved looks for user.

## GET `/looks/:lookId`

Get saved look details.

## DELETE `/looks/:lookId`

Delete saved look.

## 11. Validation & Error Codes (Suggested)

Common HTTP status usage:

- `200` OK
- `201` Created
- `400` Bad Request
- `401` Unauthorized
- `403` Forbidden
- `404` Not Found
- `409` Conflict (duplicate booking/time slot)
- `422` Validation Error
- `429` Rate Limited
- `500` Server Error

## 12. Security Notes (Baseline)

- All auth endpoints should enforce brute-force protection
- Validate and sanitize all image URLs / upload paths
- Role-check admin-only endpoints (e.g., skill verification)
- Avoid returning sensitive internal AI scoring fields to public clients unless needed

## 13. Frontend Integration Priority

Recommended implementation order for service modules in frontend:

1. Auth (`authService`)
2. Beauticians (`beauticianService`)
3. Booking (`bookingService`)
4. Wardrobe (`wardrobeService`)
5. AI Recommendations (`aiService`)
6. Looks (`lookService`)

## 14. Versioning

- Initial draft version: `v1-draft`
- Keep backward-incompatible updates under `/api/v2` when needed.
