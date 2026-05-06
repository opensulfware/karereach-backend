# KareReach AI — Version 1.0 (Backend TODO)

This document tracks the implementation progress of the Backend MVP (Laravel 13).

> [!IMPORTANT]
> **Strict Requirement:** All AI integrations MUST utilize the official `laravel/ai` package as the primary driver for Ollama/MedGemma communication.
>
> **Task Tracking:** For every task completed during implementation, the corresponding checkbox in this file MUST be marked as checked `[x]`.

## Core Implementation Rules
- [x] Use `laravel/ai` Agents for all AI-related features.
- [x] All API responses MUST use the `ApiResponse` trait.
- [x] All success/error responses MUST include a unique `SystemCode` from the registry.
- [x] Mark each task as checked `[x]` only after implementation and testing.

## 1. Authentication Module
- [x] Implement CHW registration (`name`, `phone`, `region`, `health_program_id`).
- [x] Implement Login with phone + 6-digit PIN.
- [x] Secure PIN storage using bcrypt hashing.
- [x] Configure Laravel Sanctum for token-based auth (8-hour expiry).
- [x] Add rate limiting to login endpoint (10 req/min).
- [x] Implement Logout endpoint to revoke tokens.

## 2. Consultation Management
- [x] Create consultation migration and model.
- [x] Implement `POST /consultations` (age, sex, complaint, symptoms JSON).
- [x] Implement `GET /consultations` (paginated list for authenticated user).
- [x] Implement `GET /consultations/{id}` (single record retrieval).
- [x] Implement soft status logic (`draft` -> `completed` transition handled in AI module).

## 3. Image Upload System
- [x] Implement multi-image upload (max 3 images).
- [x] Add MIME type validation (JPG, PNG, WEBP) and size validation (5MB).
- [x] Configure local storage and path persistence in `consultation_images`.

## 4. AI Analysis (Ollama + MedGemma)
- [x] Create `OllamaService` for HTTP communication with Ollama API (via `laravel/ai`).
- [x] Build `ClinicalPromptBuilder` to generate MedGemma-compatible prompts.
- [x] Implement JSON response parsing and validation.
- [x] Create `AIResult` model and persistence logic.
- [x] Implement fail-safe mechanism (fallback to referral on error).
- [x] Set 60-second timeout for AI requests (configured in `ai.php`).

## 5. API Result Retrieval
- [x] Implement `GET /consultations/{id}/result` endpoint.
- [x] Return structured analysis: `probable_conditions`, `risk_level`, `next_action`, etc.

## 6. Infrastructure & Database
- [x] Migrations for: `users`, `consultations`, `consultation_images`, `ai_results`.
- [x] Seeders with 2 test CHW accounts and 5 sample consultations.
- [x] Set up global Sanctum middleware (via `install:api`).
- [x] Implement sanitized API error responses (hide raw exceptions in production).
- [x] Configure CORS for Flutter client origins (built-in Laravel 11).
- [x] Populate `.env` and `.env.example` with project-specific variables.

---
*Maintained by OpenSulfware*
