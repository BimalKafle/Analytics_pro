# Creator Analytics Platform - Phase 1 Requirements Analysis

## Project Overview

### Purpose
Provide content creators with a centralized platform to:
- Connect content accounts
- View video analytics in one place
- Compare video performance
- Track historical growth
- Watch videos through embedded players

The platform will not host videos. It will only collect analytics and display embedded videos.

---

## Business Problem

Creators currently use:
- YouTube Studio
- TikTok Analytics
- Instagram Insights
- Facebook Creator Studio

Analytics are fragmented across platforms.

---

## Phase 1 Scope

### Included
- Creator Authentication
- YouTube OAuth Connection
- Video Import
- Analytics Import
- Video Library
- Video Detail Page
- Dashboard
- Video Comparison
- Historical Analytics Storage

### Excluded
- AI Features
- Multi-platform Support
- Competitor Analysis
- Team Collaboration
- Thumbnail Analysis
- Audience Analysis

---

## User Roles

### Creator
- Manage profile
- Connect platform
- View analytics
- Compare videos

### Admin
- View creators
- Monitor sync jobs
- Manage system settings

---

## Functional Requirements

### FR-001 User Registration
- Register
- Verify email
- Login

### FR-002 Connect YouTube Account
- OAuth authorization
- Secure token storage

### FR-003 Import Videos
Store:
- Video ID
- Title
- Description
- Thumbnail
- Published Date
- Duration
- Embed URL

### FR-004 Import Analytics
Metrics:
- Views
- Likes
- Comments
- Shares (if available)
- Watch Time
- Average View Duration
- Impressions
- CTR
- Subscribers Gained

### FR-005 Dashboard
Display:
- Total Videos
- Total Views
- Total Watch Time
- Total Subscribers Gained
- Top Performing Videos

### FR-006 Video Library
Features:
- Search
- Sort
- Filter

### FR-007 Video Detail
Display:
- Embedded Video
- Analytics Summary
- Historical Charts

### FR-008 Video Comparison
Compare two videos side-by-side.

### FR-009 Historical Tracking
Store daily analytics snapshots.

---

## Non-Functional Requirements

### Performance
- Dashboard < 3 seconds
- Video Detail < 2 seconds

### Security
- OAuth 2.0
- HTTPS
- Encrypted Tokens
- RBAC

### Availability
- 99.5% uptime target

---

## Database Design

### creators
- id
- name
- email
- password
- created_at

### platform_accounts
- id
- creator_id
- platform
- channel_id
- access_token
- refresh_token
- connected_at

### videos
- id
- creator_id
- platform_video_id
- title
- description
- thumbnail_url
- embed_url
- published_at
- duration

### analytics_snapshots
- id
- video_id
- snapshot_date
- views
- likes
- comments
- shares
- watch_time
- avg_view_duration
- impressions
- ctr
- subscribers_gained
- created_at

---

## API Endpoints

### Authentication
- POST /register
- POST /login
- POST /logout

### YouTube
- GET /youtube/connect
- GET /youtube/callback
- DELETE /youtube/disconnect

### Videos
- GET /videos
- GET /videos/{id}

### Analytics
- GET /dashboard
- GET /videos/{id}/analytics
- GET /compare

---

## Recommended Tech Stack

### Frontend
- Nuxt 3
- Tailwind CSS
- Chart.js

### Backend
- Laravel 12

### Database
- PostgreSQL

### Cache / Queue
- Redis

### Infrastructure
- Docker Compose
- AWS (future deployment)

---

## Phase 1 Success Criteria

A creator can:
- Connect a YouTube account
- Import videos automatically
- View analytics in one dashboard
- Play videos via embedded player
- Compare videos
- Track performance historically
- Use the platform without opening YouTube Studio

---

## Development Environment

### Docker Services
- app
- postgres
- redis
- queue-worker
- scheduler

### Local Setup Recommendation
- Backend + PostgreSQL + Redis in Docker
- Nuxt frontend running locally

This architecture provides a solid foundation for future AI-powered analytics and multi-platform support.
