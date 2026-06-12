import type { UseFetchOptions } from 'nuxt/app'

export const AUTH_TOKEN_COOKIE = 'auth_token'

function buildAuthHeaders(): Record<string, string> {
  const token = useCookie<string | null>(AUTH_TOKEN_COOKIE).value

  return {
    Accept: 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
  }
}

/**
 * SSR-friendly data fetching against the backend API.
 * Thin wrapper around useFetch that applies the configured API base URL
 * and the authenticated user's bearer token.
 */
export function useApi<T>(path: string, options: UseFetchOptions<T> = {}) {
  const config = useRuntimeConfig()

  return useFetch(path, {
    baseURL: config.public.apiBase,
    headers: buildAuthHeaders(),
    ...options,
  })
}

/**
 * Imperative API client for event handlers (form submits, button clicks)
 * where useFetch is not appropriate.
 */
export function useApiClient() {
  const config = useRuntimeConfig()

  return $fetch.create({
    baseURL: config.public.apiBase,
    headers: buildAuthHeaders(),
  })
}
