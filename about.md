# KareReach AI — Backend (Laravel)

> REST API powering the KareReach AI Community Health Worker Assistant. Built with Laravel 11, PHP 8.3, and integrated with Ollama + MedGemma for AI-assisted clinical triage.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 |
| Language | PHP 8.3 |
| Auth | Laravel Sanctum (token-based) |
| Database | MySQL 8 |
| AI Runtime | Ollama (local or VPS) |
| AI Model | MedGemma (via Ollama) |
| Storage | Laravel Filesystem (local / S3-compatible) |

---

## Project Structure

```
app/
  Http/
    Controllers/Api/
      AuthController.php
      ConsultationController.php
      AIAnalysisController.php
    Middleware/
      EnsureSanctumToken.php
  Models/
    User.php
    Consultation.php
    AIResult.php
  Services/
    OllamaService.php
    ClinicalPromptBuilder.php
routes/
  api.php
database/
  migrations/
  seeders/
```

---

## Prerequisites

- PHP 8.3+
- Composer
- MySQL 8
- [Ollama](https://ollama.ai) installed and running
- MedGemma model pulled in Ollama
- Laravel ai-sdk

---

## Setup

### 1. Clone and install dependencies

```bash
git clone https://github.com/opensulfware/karereach-backend.git
cd karereach-backend
composer install
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials and Ollama settings.

### 3. Run migrations and seeders

```bash
php artisan migrate
php artisan db:seed
```

This creates 2 test CHW accounts and 5 sample consultations.

### 4. Install Ollama and pull MedGemma

```bash
# macOS / Linux
curl -fsSL https://ollama.ai/install.sh | sh

# Pull the MedGemma model
ollama pull medgemma

# Start Ollama server (default port 11434)
ollama serve
```

### 5. Start the development server

```bash
php artisan serve
```

API available at `http://localhost:8000/api/v1/`

---

## Environment Variables

```env
APP_NAME=KareReach
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=karereach
DB_USERNAME=root
DB_PASSWORD=

OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_MODEL=medgemma
OLLAMA_TIMEOUT=60

SANCTUM_STATEFUL_DOMAINS=localhost
SESSION_LIFETIME=480

FILESYSTEM_DISK=local
MAX_IMAGE_SIZE=5120
```

---

## API Reference

All endpoints prefixed with `/api/v1/`. Authenticated routes require `Authorization: Bearer <token>`.

### Auth

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/auth/register` | — | Register a new CHW |
| POST | `/auth/login` | — | Login with phone + PIN |
| POST | `/auth/logout` | ✓ | Revoke current token |

### Consultations

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/consultations` | ✓ | Paginated list |
| POST | `/consultations` | ✓ | Create new consultation |
| GET | `/consultations/{id}` | ✓ | Single consultation |
| POST | `/consultations/{id}/images` | ✓ | Upload image (max 3, 5MB each) |
| POST | `/consultations/{id}/analyse` | ✓ | Trigger AI analysis |
| GET | `/consultations/{id}/result` | ✓ | Fetch AI result |

### Standard Response Envelope

```json
{
  "success": true,
  "data": {},
  "message": "string",
  "errors": {}
}
```

---

## AI Integration

`OllamaService` sends a structured clinical prompt to MedGemma and expects:

```json
{
  "probable_conditions": ["condition1", "condition2"],
  "risk_level": "low|urgent|emergency",
  "next_action": "refer|treat|monitor",
  "clinical_notes": "brief clinical reasoning",
  "red_flags": ["warning sign 1"]
}
```

If Ollama is unreachable, a safe fallback is returned with `risk_level: urgent` and `next_action: refer`.

---

## Database Schema

**users** — `id, name, phone (unique), pin_hash, region, health_program_id, is_active, last_login_at, timestamps`

**consultations** — `id, user_id (FK), patient_age, patient_sex, chief_complaint, duration_days, symptoms (JSON), notes, status, timestamps`

**consultation_images** — `id, consultation_id (FK), file_path, file_size, created_at`

**ai_results** — `id, consultation_id (FK), probable_conditions (JSON), risk_level (ENUM), next_action (ENUM), clinical_notes, red_flags (JSON), ollama_model, ollama_response_time_ms, created_at`

---

## Security

- PINs stored as bcrypt hashes — never plain text
- Sanctum tokens expire after 8 hours
- Rate limiting: 10 requests/minute on `/auth/login`
- Image validation: JPG, PNG, WEBP only; max 5MB per file
- Sanitised API errors — no raw Laravel exceptions exposed in production

---

## Test Accounts (Seeded)

| Name | Phone | PIN |
|---|---|---|
| Amara Diallo | +237600000001 | 123456 |
| Fatima Ouedraogo | +237600000002 | 654321 |

---

## License

MIT — [OpenSulfware](https://github.com/opensulfware)
