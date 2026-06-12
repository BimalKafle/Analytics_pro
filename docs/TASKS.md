# Phase 1 Task List — Creator Analytics Platform

Workflow rules:

- One task = one branch, branched from `main`.
- Branch naming: `feature/task-XX-short-name` (e.g. `feature/task-03-database-schema`).
- A task is merged back into `main` only when it is complete and verified.
- Each task should leave the project in a working state.

Status legend: `[ ]` pending · `[x]` merged into main

---

## Foundation

- [x] **Task 00 — Project task list**
  - Branch: `feature/task-00-task-list`
  - Add this task list to the repository.

- [ ] **Task 01 — Docker environment & Laravel backend scaffold**
  - Branch: `feature/task-01-backend-scaffold`
  - Docker Compose with `app`, `postgres`, `redis`, `queue-worker`, `scheduler` services.
  - Fresh Laravel 12 (PHP 8.4) project under `backend/`.
  - `.env.example`, Redis queue/cache config, health-check route.

- [ ] **Task 02 — Nuxt 3 frontend scaffold**
  - Branch: `feature/task-02-frontend-scaffold`
  - Nuxt 3 + TypeScript + Tailwind CSS under `frontend/`.
  - Base layout, API client setup pointing at the Laravel backend.

- [ ] **Task 03 — Database schema & models**
  - Branch: `feature/task-03-database-schema`
  - Migrations + Eloquent models: `creators`, `platform_accounts`, `videos`, `analytics_snapshots`.
  - Encrypted casts for OAuth tokens, indexes, model relationships, factories.

## Authentication

- [ ] **Task 04 — Authentication API (FR-001)**
  - Branch: `feature/task-04-auth-api`
  - Register, login, logout, email verification (Laravel Sanctum).
  - Request validation, consistent API response structure, rate limiting, feature tests.

- [ ] **Task 05 — Authentication frontend**
  - Branch: `feature/task-05-auth-frontend`
  - Register / login / logout pages, email-verification flow, auth store, route middleware.

## YouTube Integration

- [ ] **Task 06 — Platform provider architecture & YouTube OAuth (FR-002)**
  - Branch: `feature/task-06-youtube-oauth`
  - `PlatformProvider` contract + `YouTubeProvider` implementation.
  - `GET /youtube/connect`, `GET /youtube/callback`, `DELETE /youtube/disconnect`.
  - Tokens encrypted at rest, never exposed to clients or logs; token refresh handling.

- [ ] **Task 07 — Video import (FR-003)**
  - Branch: `feature/task-07-video-import`
  - Background job importing video metadata (ID, title, description, thumbnail URL, embed URL, published date, duration).
  - Triggered on connect + scheduled re-sync. No video files ever stored.

- [ ] **Task 08 — Analytics import & historical snapshots (FR-004, FR-009)**
  - Branch: `feature/task-08-analytics-import`
  - Background job fetching metrics (views, likes, comments, shares, watch time, avg view duration, impressions, CTR, subscribers gained).
  - Daily snapshot persistence via scheduler; snapshots are append-only.

## Core Features

- [ ] **Task 09 — Dashboard API & page (FR-005)**
  - Branch: `feature/task-09-dashboard`
  - `GET /dashboard`: totals (videos, views, watch time, subscribers gained) + top performing videos.
  - Nuxt dashboard page with Chart.js. Target load < 3s.

- [ ] **Task 10 — Video library (FR-006)**
  - Branch: `feature/task-10-video-library`
  - `GET /videos` with search, sort, filter, pagination.
  - Library page with thumbnail grid/list.

- [ ] **Task 11 — Video detail page (FR-007)**
  - Branch: `feature/task-11-video-detail`
  - `GET /videos/{id}` + `GET /videos/{id}/analytics`.
  - Embedded player, analytics summary, historical charts. Target load < 2s.

- [ ] **Task 12 — Video comparison (FR-008)**
  - Branch: `feature/task-12-video-comparison`
  - `GET /compare` for two videos; side-by-side comparison page with charts.

## Hardening & Admin

- [ ] **Task 13 — Admin role & RBAC**
  - Branch: `feature/task-13-admin-rbac`
  - Role-based access control; admin endpoints to view creators and monitor sync jobs.

- [ ] **Task 14 — Security & API hardening**
  - Branch: `feature/task-14-hardening`
  - Review rate limiting, authorization policies, error responses, CORS, secret handling.

- [ ] **Task 15 — Documentation & developer setup guide**
  - Branch: `feature/task-15-docs`
  - README with setup instructions, environment variables, API overview, common commands.
