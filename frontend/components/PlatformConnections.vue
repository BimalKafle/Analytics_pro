<script setup lang="ts">
import { storeToRefs } from 'pinia'

const platformsStore = usePlatformsStore()
const { youtubeAccount, isLoading } = storeToRefs(platformsStore)
const { fetchAccounts, getYouTubeAuthorizationUrl, disconnectYouTube, syncYouTube } = platformsStore

const route = useRoute()
const notice = ref<{ variant: 'success' | 'error', text: string } | null>(null)
const isBusy = ref(false)

const CALLBACK_NOTICES: Record<string, { variant: 'success' | 'error', text: string }> = {
  connected: { variant: 'success', text: 'YouTube connected. Your videos are being imported in the background.' },
  denied: { variant: 'error', text: 'YouTube connection was cancelled.' },
  invalid_state: { variant: 'error', text: 'The connection attempt expired. Please try again.' },
  failed: { variant: 'error', text: 'YouTube connection failed. Please try again.' },
}

onMounted(async () => {
  const flag = route.query.youtube
  if (typeof flag === 'string' && CALLBACK_NOTICES[flag]) {
    notice.value = CALLBACK_NOTICES[flag]
  }

  await fetchAccounts()
})

async function connect(): Promise<void> {
  isBusy.value = true

  try {
    // Full-page redirect to Google's consent screen.
    window.location.href = await getYouTubeAuthorizationUrl()
  } catch {
    notice.value = { variant: 'error', text: 'Could not start the YouTube connection. Please try again.' }
    isBusy.value = false
  }
}

async function disconnect(): Promise<void> {
  isBusy.value = true

  try {
    await disconnectYouTube()
    notice.value = { variant: 'success', text: 'YouTube account disconnected.' }
  } catch {
    notice.value = { variant: 'error', text: 'Could not disconnect. Please try again.' }
  } finally {
    isBusy.value = false
  }
}

async function sync(): Promise<void> {
  isBusy.value = true

  try {
    notice.value = { variant: 'success', text: await syncYouTube() }
  } catch {
    notice.value = { variant: 'error', text: 'Could not queue the import. Please try again.' }
  } finally {
    isBusy.value = false
  }
}
</script>

<template>
  <section class="space-y-4">
    <h2 class="text-lg font-semibold">Connected platforms</h2>

    <BaseAlert v-if="notice" :variant="notice.variant">
      {{ notice.text }}
    </BaseAlert>

    <div class="rounded-lg border border-gray-200 bg-white p-4">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="font-medium">YouTube</p>
          <p v-if="youtubeAccount" class="text-sm text-gray-600">
            {{ youtubeAccount.channel_name ?? youtubeAccount.channel_id }}
            <span v-if="youtubeAccount.video_count !== undefined">
              · {{ youtubeAccount.video_count }} videos
            </span>
          </p>
          <p v-else class="text-sm text-gray-600">
            Connect your channel to import videos and analytics.
          </p>
        </div>

        <div class="flex gap-2">
          <template v-if="youtubeAccount">
            <BaseButton variant="secondary" :loading="isBusy" @click="sync">
              Sync videos
            </BaseButton>
            <BaseButton variant="secondary" :loading="isBusy" @click="disconnect">
              Disconnect
            </BaseButton>
          </template>
          <BaseButton v-else variant="primary" :loading="isBusy || isLoading" @click="connect">
            Connect YouTube
          </BaseButton>
        </div>
      </div>
    </div>
  </section>
</template>
