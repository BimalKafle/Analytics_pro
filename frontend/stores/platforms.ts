import { defineStore } from 'pinia'
import type {
  AuthorizationUrlResponse,
  MessageResponse,
  PlatformAccount,
  PlatformAccountsResponse,
} from '~/types/api'

/**
 * Connected platform accounts and connection actions.
 */
export const usePlatformsStore = defineStore('platforms', () => {
  const accounts = ref<PlatformAccount[]>([])
  const isLoading = ref(false)

  const youtubeAccount = computed(
    () => accounts.value.find(account => account.platform === 'youtube') ?? null,
  )

  async function fetchAccounts(): Promise<void> {
    const client = useApiClient()
    isLoading.value = true

    try {
      const response = await client<PlatformAccountsResponse>('/platform-accounts')
      accounts.value = response.data
    } finally {
      isLoading.value = false
    }
  }

  /** Returns the Google consent URL the browser should be redirected to. */
  async function getYouTubeAuthorizationUrl(): Promise<string> {
    const client = useApiClient()
    const response = await client<AuthorizationUrlResponse>('/youtube/connect')

    return response.data.authorization_url
  }

  async function disconnectYouTube(): Promise<void> {
    const client = useApiClient()
    await client<MessageResponse>('/youtube/disconnect', { method: 'DELETE' })
    accounts.value = accounts.value.filter(account => account.platform !== 'youtube')
  }

  async function syncYouTube(): Promise<string> {
    const client = useApiClient()
    const response = await client<MessageResponse>('/youtube/sync', { method: 'POST' })

    return response.message
  }

  return {
    accounts,
    isLoading,
    youtubeAccount,
    fetchAccounts,
    getYouTubeAuthorizationUrl,
    disconnectYouTube,
    syncYouTube,
  }
})
