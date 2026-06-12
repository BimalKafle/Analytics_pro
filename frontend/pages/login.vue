<script setup lang="ts">
import type { FormErrorState } from '~/utils/apiErrors'

definePageMeta({ middleware: 'guest' })

const { login } = useAuthStore()

const form = reactive({ email: '', password: '' })
const formError = ref<FormErrorState | null>(null)
const isSubmitting = ref(false)

async function submit(): Promise<void> {
  formError.value = null
  isSubmitting.value = true

  try {
    await login(form)
    await navigateTo('/account')
  } catch (error) {
    formError.value = toFormErrorState(error)
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <section class="mx-auto max-w-md">
    <h1 class="text-2xl font-bold">Log in</h1>

    <form class="mt-6 space-y-4" novalidate @submit.prevent="submit">
      <BaseAlert v-if="formError" variant="error">
        {{ formError.message }}
      </BaseAlert>

      <BaseInput
        id="email"
        v-model="form.email"
        label="Email"
        type="email"
        autocomplete="email"
        required
        :errors="formError?.fieldErrors.email"
      />

      <BaseInput
        id="password"
        v-model="form.password"
        label="Password"
        type="password"
        autocomplete="current-password"
        required
        :errors="formError?.fieldErrors.password"
      />

      <BaseButton type="submit" variant="primary" :loading="isSubmitting" class="w-full">
        Log in
      </BaseButton>
    </form>

    <p class="mt-4 text-sm text-gray-600">
      Don't have an account?
      <NuxtLink to="/register" class="font-medium text-blue-600 hover:underline">
        Register
      </NuxtLink>
    </p>
  </section>
</template>
