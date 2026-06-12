<script setup lang="ts">
const props = defineProps<{
  type?: 'button' | 'submit'
  variant?: 'primary' | 'secondary'
  loading?: boolean
  disabled?: boolean
}>()

const variantClasses = computed(() =>
  props.variant === 'secondary'
    ? 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
    : 'bg-blue-600 text-white hover:bg-blue-700',
)

const isDisabled = computed(() => props.disabled || props.loading)
</script>

<template>
  <button
    :type="type ?? 'button'"
    :disabled="isDisabled"
    class="inline-flex items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-blue-300 disabled:cursor-not-allowed disabled:opacity-60"
    :class="variantClasses"
  >
    <span
      v-if="loading"
      class="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"
      aria-hidden="true"
    />
    <slot />
  </button>
</template>
