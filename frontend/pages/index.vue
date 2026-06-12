<script setup lang="ts">
import type { HealthResponse } from '~/types/api'

const { data: health, error } = await useApi<HealthResponse>('/health')

const backendStatus = computed(() => {
  if (error.value) {
    return 'unreachable'
  }

  return health.value?.status ?? 'unknown'
})
</script>

<template>
  <section class="mx-auto max-w-xl text-center">
    <h1 class="text-3xl font-bold tracking-tight">
      Creator Analytics Platform
    </h1>
    <p class="mt-3 text-gray-600">
      Connect your platforms and analyze all of your content from a single
      dashboard.
    </p>

    <div class="mt-8 inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm">
      <span
        class="h-2.5 w-2.5 rounded-full"
        :class="backendStatus === 'ok' ? 'bg-green-500' : 'bg-red-500'"
        aria-hidden="true"
      />
      <span>Backend: {{ backendStatus }}</span>
    </div>
  </section>
</template>
