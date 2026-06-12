<script setup lang="ts">
import { storeToRefs } from 'pinia'

const { user, isAuthenticated } = storeToRefs(useAuthStore())
</script>

<template>
  <div class="flex min-h-screen flex-col bg-gray-50 text-gray-900">
    <header class="border-b border-gray-200 bg-white">
      <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6">
        <NuxtLink to="/" class="text-lg font-semibold">
          Creator Analytics
        </NuxtLink>
        <nav aria-label="Main navigation" class="flex items-center gap-4 text-sm">
          <template v-if="isAuthenticated">
            <NuxtLink to="/account" class="text-gray-600 hover:text-gray-900">
              {{ user?.name }}
            </NuxtLink>
          </template>
          <template v-else>
            <NuxtLink to="/login" class="text-gray-600 hover:text-gray-900">
              Log in
            </NuxtLink>
            <NuxtLink
              to="/register"
              class="rounded-md bg-blue-600 px-3 py-1.5 font-medium text-white hover:bg-blue-700"
            >
              Register
            </NuxtLink>
          </template>
        </nav>
      </div>
    </header>

    <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6">
      <slot />
    </main>

    <footer class="border-t border-gray-200 bg-white">
      <div class="mx-auto max-w-7xl px-4 py-4 text-sm text-gray-500 sm:px-6">
        Creator Analytics Platform
      </div>
    </footer>
  </div>
</template>
