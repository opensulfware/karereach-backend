# KareReach AI — MVP & Roadmap Plan

> This document outlines the phased development plan for KareReach AI. The MVP covers core features needed to put the app in the hands of community health workers. Versions 2 and 3 extend the platform with richer intelligence, supervision tools, and health system integrations.

---

## MVP — Version 1.0

The MVP is scoped to the minimum set of features that deliver real clinical value to a CHW working in a low-resource setting. Both backend and frontend must ship together for the MVP to be functional.

---

### Backend (Laravel) — MVP Features

#### 1. Authentication Module
- CHW registration: name, phone, region, health program ID
- Login with phone + 6-digit PIN
- PIN stored as bcrypt hash — never plain text
- Sanctum token issued on login, expires after 8 hours
- Rate limiting on login endpoint (10 requests/minute)
- Logout endpoint to revoke token

#### 2. Consultation Management
- Create a new consultation record with: patient age, sex, chief complaint, duration, symptom list (JSON), and optional notes
- Retrieve paginated list of consultations for the authenticated CHW
- Retrieve single consultation by ID
- Soft consultation status: `draft` while in progress, `completed` after AI analysis

#### 3. Image Upload
- Accept up to 3 images per consultation (multipart/form-data)
- Validate MIME type (JPG, PNG, WEBP only) and file size (max 5MB each)
- Store images on Laravel filesystem (local disk for MVP)
- Return stored file path for AI prompt construction

#### 4. AI Analysis via Ollama + MedGemma
- `OllamaService`: communicates with Ollama HTTP API (`POST /api/chat`)
- `ClinicalPromptBuilder`: constructs structured clinical prompt from consultation data
- Parse and validate JSON response from MedGemma
- Persist AI result in `ai_results` table
- Safe fallback if Ollama is unreachable: `risk_level: urgent`, `next_action: refer`
- 60-second HTTP timeout enforced

#### 5. Result Retrieval
- Endpoint to fetch AI result for a given consultation
- Response includes: `probable_conditions`, `risk_level`, `next_action`, `clinical_notes`, `red_flags`

#### 6. Database & Seeders
- Migrations for: `users`, `consultations`, `consultation_images`, `ai_results`
- Seeder with 2 test CHW accounts and 5 sample consultations

#### 7. Security & Infrastructure
- Sanctum middleware on all protected routes
- Sanitised error responses — no raw exceptions exposed
- CORS configured for Flutter app origin
- `.env.example` with all required variables

---

### Frontend (Flutter) — MVP Features

#### 1. Authentication Screens
- Login screen: phone + PIN input, form validation, error states
- Register screen: name, phone, region, health program ID
- Secure token storage via `flutter_secure_storage`
- Auto-logout after 8 hours of inactivity
- GoRouter auth guard — unauthenticated users redirected to login

#### 2. Home Screen
- Bottom navigation bar: Home, New Consultation, History, Profile
- Home dashboard showing recent consultation summary and CHW name

#### 3. Symptom Intake Form
- Chief complaint text field
- Patient age and sex inputs
- Duration selector
- Multi-select symptom checklist (fever, cough, rash, pain, swelling, wounds, eye issues, breathing difficulty)
- Optional free-text notes
- Voice input via `speech_to_text` (transcribed locally)
- Full form validation before submission

#### 4. Image Upload
- Camera capture and gallery selection via `image_picker`
- Up to 3 images per consultation
- Image preview with remove option
- Upload to backend as multipart/form-data via Dio

#### 5. AI Result Screen
- Colour-coded risk badge (green / amber / red)
- Probable conditions list
- Recommended next action
- Clinical notes and red flags
- Shimmer skeleton loading state during analysis

#### 6. Consultation History
- Paginated list: date, chief complaint, risk level
- Tap to view full consultation detail
- Offline cache: last 50 consultations stored in sqflite

#### 7. Referral Slip
- PDF generation with: patient info, symptoms, AI assessment, CHW name, timestamp, QR code
- Share via WhatsApp or save to device

#### 8. Offline Mode
- `connectivity_plus` monitors network state
- Offline banner shown when disconnected
- AI analysis button disabled offline
- Offline-created consultations queued and synced on reconnect

#### 9. UI Foundations
- Primary colour `#0F6E56`, accent `#5DCAA5`
- Shimmer loading states on all list screens
- Inline form validation errors
- Error states with retry buttons — no raw error messages shown to CHWs
- English and French localisation (flutter_localizations + arb files)

---

## Version 2.0 — Enhanced Intelligence & Supervision

> After MVP validation with real CHWs, V2 expands AI capabilities and introduces supervisor oversight.

### Backend (Laravel) — V2

- **Supervisor accounts**: role-based access (CHW vs Supervisor), supervisors can view all consultations in their region
- **Consultation feedback loop**: supervisors can annotate AI results with clinical feedback, which feeds back into prompt improvement
- **Follow-up tracking**: mark consultations as followed up, record patient outcome (recovered, referred, hospitalised)
- **Aggregate analytics API**: counts by risk level, action taken, region, and health program — for supervisor dashboards
- **Push notifications**: Firebase Cloud Messaging integration to alert CHWs of new guidance or urgent follow-ups
- **S3-compatible image storage**: migrate from local disk to object storage (MinIO or AWS S3)
- **Webhook support**: emit events on high-risk consultations to external health information systems
- **Audit logging**: track all API actions per user for compliance

### Frontend (Flutter) — V2

- **Supervisor dashboard**: region-level overview with charts (risk distribution, consultation volume, action breakdown)
- **Follow-up screen**: log patient outcomes, mark consultations as resolved
- **Notification centre**: in-app alerts for supervisor feedback and urgent case flags
- **Offline sync improvements**: conflict resolution for consultations edited on multiple devices
- **Search and filter**: filter history by date range, risk level, symptom, or action
- **Dark mode**: system-aware dark/light theme toggle
- **Biometric login**: fingerprint / face unlock as alternative to PIN
- **iOS support**: extend Flutter target to iOS 14+

---

## Version 3.0 — Health System Integration & Population Intelligence

> V3 positions KareReach as a data platform connected to national health infrastructure.

### Backend (Laravel) — V3

- **FHIR R4 compliance**: expose consultation and patient data via FHIR-compatible endpoints for integration with national health information systems (DHIS2, OpenMRS, OpenHIM)
- **Multi-model AI support**: plug in additional models (Llama 3 Med, BioMistral) and allow per-region model configuration
- **Differential diagnosis refinement**: multi-turn AI dialogue — CHW can ask follow-up questions in the result screen
- **Drug and dosage guidance**: extend AI prompt to include treatment protocol lookup based on WHO essential medicines list
- **Epidemic signal detection**: aggregate symptom patterns across regions to flag potential outbreak clusters and alert health authorities
- **CHW performance analytics**: consultation volume, referral accuracy, response time per CHW — for health program managers
- **API versioning**: maintain V1 while shipping V2 API for new clients
- **Multi-tenancy**: support multiple health programs / NGOs on a single instance with data isolation

### Frontend (Flutter) — V3

- **Multi-turn AI chat**: after initial result, CHW can ask follow-up questions (e.g. "what if the patient is pregnant?")
- **Treatment protocol viewer**: in-app drug dosage and treatment guidance cards linked to AI recommendations
- **Community health map**: GPS-tagged consultations visualised on a regional map for supervisors
- **CHW training module**: in-app microlearning cards tied to common conditions seen in their region
- **Outbreak alert banner**: push-triggered alerts when epidemic signals are detected in the CHW's region
- **Web companion app**: Flutter Web dashboard for program managers and supervisors
- **Tablet-optimised layout**: two-column layout for tablets used in health facilities
- **Voice-first mode**: full voice navigation for low-literacy CHWs

---

## Summary Table

| Feature Area | MVP (V1) | V2 | V3 |
|---|---|---|---|
| CHW auth (phone + PIN) | ✓ | | |
| Symptom intake + image upload | ✓ | | |
| AI triage via MedGemma | ✓ | | |
| Referral slip PDF | ✓ | | |
| Offline-first cache | ✓ | | |
| Supervisor accounts | | ✓ | |
| Follow-up & outcome tracking | | ✓ | |
| Push notifications | | ✓ | |
| Supervisor analytics dashboard | | ✓ | |
| FHIR / DHIS2 integration | | | ✓ |
| Multi-turn AI dialogue | | | ✓ |
| Epidemic signal detection | | | ✓ |
| Drug & dosage guidance | | | ✓ |
| Multi-tenancy (NGO support) | | | ✓ |

---

*Built by [OpenSulfware](https://github.com/opensulfware) — open-source technology for the communities that need it most.*
