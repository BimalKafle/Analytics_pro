import type { UseFetchOptions } from 'nuxt/app'

/**
 * SSR-friendly data fetching against the backend API.
 * Thin wrapper around useFetch that applies the configured API base URL.
 */
export function useApi<T>(path: string, options: UseFetchOptions<T> = {}) {
  const config = useRuntimeConfig()

  return useFetch(path, {
    baseURL: config.public.apiBase,
    headers: { Accept: 'application/json' },
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
    headers: { Accept: 'application/json' },
  })
}
