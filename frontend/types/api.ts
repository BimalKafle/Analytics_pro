export interface HealthResponse {
  status: 'ok' | 'degraded'
  dependencies: {
    database: boolean
    redis: boolean
  }
}

export interface User {
  id: number
  name: string
  email: string
  role: 'creator' | 'admin'
  email_verified: boolean
  created_at: string
}

export interface AuthResponse {
  message?: string
  data: {
    user: User
    token: string
  }
}

export interface UserResponse {
  data: User
}

export interface MessageResponse {
  message: string
}

/** Laravel validation error response (HTTP 422). */
export interface ValidationErrorResponse {
  message: string
  errors: Record<string, string[]>
}

export interface PlatformAccount {
  id: number
  platform: 'youtube'
  channel_id: string
  channel_name: string | null
  connected_at: string
  video_count?: number
}

export interface PlatformAccountsResponse {
  data: PlatformAccount[]
}

export interface AuthorizationUrlResponse {
  data: {
    authorization_url: string
  }
}
