export interface HealthResponse {
  status: 'ok' | 'degraded'
  dependencies: {
    database: boolean
    redis: boolean
  }
}
