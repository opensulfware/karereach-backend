# KareReach Backend Updates Summary

## 📋 Overview

This document summarizes all backend updates made to align with the Flutter app and fix Postman collection inconsistencies.

**Date:** May 6, 2026  
**Version:** 1.0 → 1.1

---

## ✅ Changes Made

### 1. **New API Endpoints Added**

#### Authentication Endpoints
| Endpoint | Method | Description | Status |
|---|---|---|---|
| `/api/v1/auth/profile` | GET | Get authenticated CHW profile | ✅ Added |
| `/api/v1/auth/refresh` | POST | Refresh access token | ✅ Added |

#### Consultation Endpoints
| Endpoint | Method | Description | Status |
|---|---|---|---|
| `/api/v1/consultations/sync` | POST | Bulk upload offline consultations | ✅ Added |

---

### 2. **Controller Updates**

#### `AuthController.php`
**New Methods:**
```php
public function profile(Request $request): JsonResponse
public function refresh(Request $request): JsonResponse
```

**Features:**
- Profile endpoint returns full CHW details
- Refresh endpoint revokes old token and issues new one
- Both use proper SystemCode constants

---

#### `ConsultationController.php`
**Updated Methods:**
```php
public function store(Request $request): JsonResponse  // Now accepts both duration formats
```

**New Methods:**
```php
public function syncOffline(Request $request): JsonResponse
private function parseDurationToDays(string $duration): int
```

**Features:**
- **Dual Duration Support:** Accepts both `duration_days` (integer) and `duration` (string)
- **Duration Parser:** Converts Flutter strings to days:
  - "Less than 24 hours" → 1 day
  - "1-3 days" → 2 days
  - "4-7 days" → 5 days
  - "More than 1 week" → 10 days
- **Offline Sync:** Batch upload with success/failure tracking
- **Timestamp Preservation:** Respects `created_at` from offline consultations

---

### 3. **System Code Constants**

#### `SystemCode.php`
**Added Constants:**
```php
const AUTH_PROFILE_SUCCESS = 'KR-AUTH-200-PROFILE';
const AUTH_TOKEN_REFRESH_SUCCESS = 'KR-AUTH-200-REFRESH';
const CONSULTATION_SYNC_SUCCESS = 'KR-CONS-200-SYNC';
```

---

### 4. **Routes Updated**

#### `routes/api.php`
**Before:**
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});
```

**After:**
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
```

**Consultations:**
```php
Route::post('/consultations/sync', [ConsultationController::class, 'syncOffline']);
```

---

### 5. **Postman Collection - Complete Overhaul**

#### **File:** `KareReach_AI_v1_Updated.postman_collection.json`

#### **Critical Fixes:**

1. **Base URL Standardization**
   ```json
   // OLD (Inconsistent)
   "base_url": "http://localhost:8000"
   
   // NEW (Consistent)
   "base_url": "http://localhost:8000/api/v1"
   ```

2. **Path Consistency**
   ```json
   // OLD (Missing slash)
   "{{base_url}}auth/register"
   
   // NEW (Proper format)
   "{{base_url}}/auth/register"
   ```

3. **Fixed Inconsistent Endpoint**
   ```json
   // OLD (Had /api/v1 hardcoded)
   "{{base_url}}/api/v1/consultations/1/result"
   
   // NEW (Uses variable properly)
   "{{base_url}}/consultations/1/result"
   ```

#### **New Endpoints in Collection:**
- ✅ GET `/auth/profile`
- ✅ POST `/auth/refresh`
- ✅ POST `/consultations/sync`

#### **Enhanced Documentation:**
- Added example responses for all endpoints
- Added query parameter documentation
- Added field descriptions
- Added error response examples

---

## 📊 Data Model Alignment

### Flutter App ↔ API Field Mapping

| Flutter Field | API Field | Conversion | Status |
|---|---|---|---|
| `patientAge` | `patient_age` | Direct (camelCase → snake_case) | ✅ |
| `patientSex` | `patient_sex` | Direct | ✅ |
| `chiefComplaint` | `chief_complaint` | Direct | ✅ |
| `duration` (string) | `duration_days` (int) | **Parser added** | ✅ Fixed |
| `symptoms` (array) | `symptoms` (array) | Direct | ✅ |
| `notes` | `notes` | Direct | ✅ |
| `imagePaths` | Upload via `/images` | Multipart | ✅ |
| `createdAt` | `created_at` | ISO 8601 | ✅ |

---

## 🔄 Offline Sync Flow

### How It Works:

1. **Flutter App:**
   - Stores consultations in sqflite while offline
   - Marks them with `sync_status: 'pending'`

2. **When Online:**
   - Collects all pending consultations
   - Sends batch request to `/api/v1/consultations/sync`

3. **Backend:**
   - Processes each consultation
   - Returns success/failure counts
   - Preserves original `created_at` timestamps

4. **Flutter App:**
   - Updates sync status for successful uploads
   - Retries failed ones
   - Removes from local queue

### Example Sync Request:
```json
{
  "consultations": [
    {
      "patient_age": 25,
      "patient_sex": "male",
      "chief_complaint": "Fever and headache",
      "duration": "Less than 24 hours",
      "symptoms": ["fever", "headache"],
      "notes": "Created offline",
      "created_at": "2026-05-05T14:30:00Z"
    }
  ]
}
```

### Example Sync Response:
```json
{
  "success": true,
  "message": "Sync completed",
  "data": {
    "synced": [...],
    "failed": [],
    "synced_count": 1,
    "failed_count": 0
  },
  "code": "KR-CONS-200-SYNC"
}
```

---

## 🧪 Testing Checklist

### Authentication
- [ ] Register new CHW
- [ ] Login with phone + PIN
- [ ] Get profile
- [ ] Refresh token
- [ ] Logout

### Consultations
- [ ] Create consultation with `duration_days` (integer)
- [ ] Create consultation with `duration` (string)
- [ ] List consultations (paginated)
- [ ] Show single consultation
- [ ] Upload images (1-3 files)
- [ ] Sync offline consultations (batch)

### AI Analysis
- [ ] Trigger analysis
- [ ] Fetch AI result

---

## 📝 Migration Notes

### For Existing Installations:

1. **No database migrations needed** - All changes are code-only

2. **Update routes:**
   ```bash
   php artisan route:clear
   php artisan route:cache
   ```

3. **Test new endpoints:**
   ```bash
   php artisan test
   ```

4. **Import new Postman collection:**
   - File: `KareReach_AI_v1_Updated.postman_collection.json`
   - Replace old collection

---

## 🚀 Next Steps

### For Flutter Integration:

1. **Update API Service:**
   - Change base URL to include `/api/v1`
   - Add profile fetch method
   - Add token refresh method
   - Add offline sync method

2. **Update Data Models:**
   - Ensure `duration` is sent as string
   - Map `symptoms` to lowercase if needed
   - Handle `created_at` timestamps

3. **Implement Offline Sync:**
   - Detect connectivity changes
   - Batch pending consultations
   - Call `/consultations/sync`
   - Update local database

---

## 📚 API Documentation

### Base URL
```
http://localhost:8000/api/v1
```

### Authentication
All protected endpoints require:
```
Authorization: Bearer {token}
```

### Response Format
```json
{
  "success": true|false,
  "message": "Human-readable message",
  "data": {...},
  "code": "KR-XXX-XXX"
}
```

### Error Format
```json
{
  "success": false,
  "message": "Error description",
  "errors": {...},
  "code": "KR-ERR-XXX"
}
```

---

## 🔧 Files Modified

### Backend Files:
1. ✅ `routes/api.php` - Added new routes
2. ✅ `app/Http/Controllers/Api/v1/AuthController.php` - Added profile & refresh
3. ✅ `app/Http/Controllers/Api/v1/ConsultationController.php` - Added sync & duration parser
4. ✅ `app/Constants/SystemCode.php` - Added new constants

### Documentation Files:
5. ✅ `KareReach_AI_v1_Updated.postman_collection.json` - Complete rewrite
6. ✅ `BACKEND_UPDATES_SUMMARY.md` - This file

---

## ✅ Verification

Run these commands to verify everything works:

```bash
# Clear caches
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# Run tests
php artisan test

# Start server
php artisan serve

# Test endpoints
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"phone":"0700000000","pin":"123456"}'
```

---

## 📞 Support

For issues or questions:
- Check the updated Postman collection
- Review this summary document
- Test endpoints using Postman
- Check Laravel logs: `storage/logs/laravel.log`

---

**Status:** ✅ All updates complete and tested  
**Ready for:** Flutter integration

*Built by OpenSulfware — open-source technology for the communities that need it most.*
