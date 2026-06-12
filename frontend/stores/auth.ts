import { defineStore } from 'pinia'
import type { AuthResponse, MessageResponse, User, UserResponse } from '~/types/api'

export interface RegisterPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface LoginPayload {
  email: string
  password: string
}

const TOKEN_MAX_AGE_SECONDS = 60 * 60 * 24 * 30

/**
 * Authentication state and actions.
 * The token lives in an SSR-readable cookie; the user lives in store state.
 */
export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = useCookie<string | null>(AUTH_TOKEN_COOKIE, {
    maxAge: TOKEN_MAX_AGE_SECONDS,
    sameSite: 'lax',
  })

  const isAuthenticated = computed(() => user.value !== null)

  function applySession(response: AuthResponse): void {
    token.value = response.data.token
    user.value = response.data.user
  }

  async function register(payload: RegisterPayload): Promise<void> {
    const client = useApiClient()
    applySession(await client<AuthResponse>('/register', { method: 'POST', body: payload }))
  }

  async function login(payload: LoginPayload): Promise<void> {
    const client = useApiClient()
    applySession(await client<AuthResponse>('/login', { method: 'POST', body: payload }))
  }

  async function logout(): Promise<void> {
    const client = useApiClient()

    try {
      await client<MessageResponse>('/logout', { method: 'POST' })
    } finally {
      // Always clear the local session, even if the API call fails.
      token.value = null
      user.value = null
    }
  }

  /** Load the current user from the API when a token exists but state is empty. */
  async function fetchUser(): Promise<void> {
    if (!token.value || user.value) {
      return
    }

    const client = useApiClient()

    try {
      const response = await client<UserResponse>('/user')
      user.value = response.data
    } catch {
      // Token is invalid or expired; drop it so the app treats the visitor as guest.
      token.value = null
      user.value = null
    }
  }

  async function resendVerificationEmail(): Promise<string> {
    const client = useApiClient()
    const response = await client<MessageResponse>('/email/verification-notification', { method: 'POST' })

    return response.message
  }

  async function refreshUser(): Promise<void> {
    user.value = null
    await fetchUser()
  }

  return {
    user,
    isAuthenticated,
    register,
    login,
    logout,
    fetchUser,
    refreshUser,
    resendVerificationEmail,
  }
})
