# PROJECT_CONTEXT.md

# Creator Analytics Platform

## 1. Project Overview

### Project Name
Creator Analytics Platform

### Purpose
A centralized analytics platform that allows content creators to connect their content platforms and analyze video performance from a single dashboard.

The platform does not host, process, or store video files.

The platform only stores:
- Video metadata
- Thumbnail URLs
- Embed URLs
- Analytics data
- Historical analytics snapshots

---

## 2. Product Vision

Enable creators to understand the performance of all their content from a single interface without needing to switch between platform-specific dashboards.

Phase 1 focuses on becoming the creator's central analytics hub.

AI-powered insights, recommendations, and content intelligence will be introduced in later phases.

---

## 3. Current Development Phase

### Active Phase
Phase 1 (MVP)

### Goal
Provide a unified YouTube analytics dashboard with historical tracking.

---

## 4. Phase 1 Scope

### Included Features
- Authentication
- YouTube OAuth Integration
- Video Metadata Import
- Analytics Import
- Video Library
- Video Detail Page
- Embedded Video Playback
- Dashboard
- Video Comparison
- Historical Analytics Tracking

### Excluded Features
- AI Recommendations
- AI Insights
- Team Collaboration
- Competitor Tracking
- Audience Analysis
- Thumbnail Analysis

---

## 5. Supported Platforms

### Phase 1
- YouTube

### Future Platforms
- TikTok
- Instagram
- Facebook
- Vimeo
- Twitch

Architecture must support future platform expansion.

---

## 6. Product Rules

### Video Storage Rule
Video files must never be downloaded or stored.

Only store:
- Platform video ID
- Metadata
- Thumbnail URL
- Embed URL

### Analytics Rule
Analytics must be stored locally.

Historical analytics snapshots must be preserved.

### Synchronization Rule
Analytics synchronization must run through background jobs.

Analytics synchronization must never block user requests.

### Platform Independence Rule
Platform-specific logic must be isolated and extensible.

---

## 7. Technical Stack

### Frontend
- Nuxt 3
- TypeScript
- Tailwind CSS

### Backend
- Laravel 12
- PHP 8.4+

### Database
- PostgreSQL

### Queue / Cache
- Redis

### Infrastructure
- Docker Compose

---

## 8. Development Environment

Required Docker services:
- app
- postgres
- redis
- queue-worker
- scheduler

Frontend may run locally during development.

---

## 9. Architecture Guidelines

### Backend
- Controllers must remain thin
- Business logic belongs in services
- Platform integrations belong in providers

### Provider Examples
- YouTubeProvider
- TikTokProvider
- InstagramProvider

Future platforms should be added via providers without modifying existing implementations.

### Data Design
Historical analytics snapshots are a core business asset and must be preserved.

---

## 10. Security Requirements

### OAuth
- Use OAuth 2.0
- Encrypt tokens at rest
- Never expose tokens to clients
- Never log tokens

### API Security
- Authentication required
- Authorization enforced
- Rate limiting enabled

---

## 11. Success Criteria

Phase 1 is successful when a creator can:
- Connect a YouTube account
- Import videos automatically
- View analytics from one dashboard
- View historical performance
- Play videos through embedded players
- Compare video performance

---

## 12. Long-Term Vision

### Phase 2
- AI insights
- Trend analysis
- Content recommendations

### Phase 3
- Multi-platform analytics
- Cross-platform reporting

### Phase 4
- Team collaboration
- Agency accounts
- White-label solutions

The architecture created in Phase 1 should support future phases without major rewrites.
