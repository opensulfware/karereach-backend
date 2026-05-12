# 🚀 Quick Start - Backend Updates

## ✅ What Was Updated

### 1. **New API Endpoints** (3 added)
- `GET /api/v1/auth/profile` - Get CHW profile
- `POST /api/v1/auth/refresh` - Refresh access token  
- `POST /api/v1/consultations/sync` - Bulk upload offline consultations

### 2. **Enhanced Existing Endpoints**
- `POST /api/v1/consultations` - Now accepts both `duration_days` (int) and `duration` (string)

### 3. **Fixed Postman Collection**
- ✅ Base URL now includes `/api/v1` prefix
- ✅ All paths standardized with leading slash
- ✅ Added example responses
- ✅ Added new endpoints

---

## 📦 Installation (If Not Done)

```bash
cd karereach-backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Start server
php artisan serve
```

---

## 🧪 Quick Test

### 1. **Test Login**
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"phone":"0700000000","pin":"123456"}'
```

### 2. **Test Profile** (use token from login)
```bash
curl -X GET http://localhost:8000/api/v1/auth/profile \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 3. **Test Consultation with String Duration**
```bash
curl -X POST http://localhost:8000/api/v1/consultations \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "patient_age": 25,
    "patient_sex": "male",
    "chief_complaint": "Fever",
    "duration": "Less than 24 hours",
    "symptoms": ["fever", "headache"]
  }'
```

---

## 📋 Files Changed

| File | Changes |
|---|---|
| `routes/api.php` | Added 3 new routes |
| `app/Http/Controllers/Api/v1/AuthController.php` | Added `profile()` and `refresh()` methods |
| `app/Http/Controllers/Api/v1/ConsultationController.php` | Added `syncOffline()` and `parseDurationToDays()` |
| `app/Constants/SystemCode.php` | Added 3 new constants |
| `KareReach_AI_v1_Updated.postman_collection.json` | Complete rewrite with fixes |

---

## 🎯 Next Steps

### For Backend:
1. ✅ Install dependencies: `composer install`
2. ✅ Run migrations: `php artisan migrate`
3. ✅ Test new endpoints with Postman
4. ✅ Verify duration parser works

### For Flutter:
1. Update base URL to `http://10.0.0.0:8000/api/v1` (Android emulator)
2. Add profile fetch on app start
3. Implement token refresh before expiry
4. Implement offline sync on connectivity restore
5. Send `duration` as string (e.g., "1-3 days")

---

## 📚 Documentation

- **Full Summary:** `BACKEND_UPDATES_SUMMARY.md`
- **Postman Collection:** `KareReach_AI_v1_Updated.postman_collection.json`
- **API Docs:** See Postman collection descriptions

---

## ✅ Verification Checklist

- [ ] Backend dependencies installed
- [ ] Database migrated
- [ ] Server running on port 8000
- [ ] Login endpoint works
- [ ] Profile endpoint works
- [ ] Refresh endpoint works
- [ ] Consultation with string duration works
- [ ] Sync endpoint works
- [ ] Postman collection imported

---

## 🐛 Troubleshooting

### "vendor/autoload.php not found"
```bash
composer install
```

### "Route not found"
```bash
php artisan route:clear
php artisan route:cache
```

### "Token expired"
```bash
# Use the refresh endpoint
curl -X POST http://localhost:8000/api/v1/auth/refresh \
  -H "Authorization: Bearer OLD_TOKEN"
```

---

**Status:** ✅ Ready for Flutter integration  
**Version:** 1.1  
**Date:** May 6, 2026
