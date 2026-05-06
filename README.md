# KareReach AI — Backend

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Laravel 13](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel)](https://laravel.com)
[![PHP 8.3](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php)](https://php.net)

> **Empowering Community Health Workers with Local AI Intelligence.**

KareReach AI is a high-performance REST API designed to support Community Health Workers (CHWs) in low-resource settings. By integrating **Ollama** and the **MedGemma** clinical model, it provides automated triage, risk assessment, and referral guidance—even without constant internet access.

---

## 🚀 Key Features

- **Clinical AI Triage**: Real-time analysis of symptoms via MedGemma.
- **Offline-First Workflow**: Designed to sync with Flutter clients once connectivity is restored.
- **Secure CHW Access**: Phone + PIN authentication optimized for field workers.
- **Visual Evidence**: Multi-image upload support for clinical documentation.
- **Standardized Responses**: Consistent JSON envelopes with fallback mechanisms for high-reliability.

## 🛠 Tech Stack

- **Framework**: Laravel 13
- **Runtime**: PHP 8.3
- **AI Engine**: Ollama (MedGemma Model)
- **Database**: MySQL 8 / SQLite
- **Auth**: Laravel Sanctum
- **Storage**: Local / S3-compatible

## 📁 Project Structure

```text
app/
├── Http/Controllers/Api/   # API Logic (Auth, Consultations, AI)
├── Services/               # Ollama & Clinical Prompt Logic
└── Models/                 # User, Consultation, AIResult
routes/api.php              # Versioned API routes
database/                   # Migrations and clinical seeders
```

## ⚙️ Getting Started

### Prerequisites
- PHP 8.3+ & Composer
- MySQL or SQLite
- [Ollama](https://ollama.ai) installed with `medgemma` model pulled.

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/opensulfware/karereach-backend.git
   cd karereach-backend
   ```

2. **Run the setup script:**
   ```bash
   composer run setup
   ```
   *This command installs dependencies, sets up the environment, and runs migrations.*

3. **Start the AI engine:**
   ```bash
   ollama pull medgemma
   ollama serve
   ```

4. **Run the development server:**
   ```bash
   composer run dev
   ```

## 📚 API Overview

| Endpoint | Method | Description |
|---|---|---|
| `/api/v1/auth/login` | POST | Authenticate with Phone + PIN |
| `/api/v1/consultations` | POST | Create a new intake record |
| `/api/v1/consultations/{id}/analyse` | POST | Trigger MedGemma analysis |
| `/api/v1/consultations/{id}/result` | GET | Fetch AI risk assessment |

See the [Full Roadmap](./roadmap.md) for upcoming features in V2 and V3.

## 📄 License

Distributed under the **MIT License**. See `LICENSE` for more information. KareReach is an [OpenSulfware](https://github.com/opensulfware) initiative.

---
*Built for communities that need technology most.*
