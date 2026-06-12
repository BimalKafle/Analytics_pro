<template>
  <div class="flex min-h-screen flex-col bg-gray-50 text-gray-900">
    <header class="border-b border-gray-200 bg-white">
      <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6">
        <NuxtLink to="/" class="text-lg font-semibold">
          Creator Analytics
        </NuxtLink>
        <nav aria-label="Main navigation" class="flex items-center gap-4 text-sm">
          <!-- Navigation links are added as features land (dashboard, library, compare). -->
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
