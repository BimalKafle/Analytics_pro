<script setup lang="ts">
const props = defineProps<{
  variant: 'error' | 'success' | 'info'
}>()

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'error':
      return 'border-red-200 bg-red-50 text-red-800'
    case 'success':
      return 'border-green-200 bg-green-50 text-green-800'
    default:
      return 'border-blue-200 bg-blue-50 text-blue-800'
  }
})
</script>

<template>
  <div role="alert" class="rounded-md border px-4 py-3 text-sm" :class="variantClasses">
    <slot />
  </div>
</template>
