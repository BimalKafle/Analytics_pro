<script setup lang="ts">
const props = defineProps<{
  id: string
  label: string
  type?: string
  autocomplete?: string
  required?: boolean
  errors?: string[]
}>()

const model = defineModel<string>({ required: true })

const inputType = computed(() => props.type ?? 'text')
const hasError = computed(() => (props.errors?.length ?? 0) > 0)
</script>

<template>
  <div>
    <label :for="id" class="block text-sm font-medium text-gray-700">
      {{ label }}
    </label>
    <input
      :id="id"
      v-model="model"
      :type="inputType"
      :autocomplete="autocomplete"
      :required="required"
      :aria-invalid="hasError"
      :aria-describedby="hasError ? `${id}-error` : undefined"
      class="mt-1 block w-full rounded-md border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2"
      :class="hasError
        ? 'border-red-300 focus:border-red-500 focus:ring-red-200'
        : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200'"
    >
    <p v-if="hasError" :id="`${id}-error`" class="mt-1 text-sm text-red-600">
      {{ errors![0] }}
    </p>
  </div>
</template>
