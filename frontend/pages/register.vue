<script setup lang="ts">
import type { FormErrorState } from '~/utils/apiErrors'

definePageMeta({ middleware: 'guest' })

const { register } = useAuthStore()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})
const formError = ref<FormErrorState | null>(null)
const isSubmitting = ref(false)

async function submit(): Promise<void> {
  formError.value = null
  isSubmitting.value = true

  try {
    await register(form)
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
    <h1 class="text-2xl font-bold">Create your account</h1>

    <form class="mt-6 space-y-4" novalidate @submit.prevent="submit">
      <BaseAlert v-if="formError" variant="error">
        {{ formError.message }}
      </BaseAlert>

      <BaseInput
        id="name"
        v-model="form.name"
        label="Name"
        autocomplete="name"
        required
        :errors="formError?.fieldErrors.name"
      />

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
        autocomplete="new-password"
        required
        :errors="formError?.fieldErrors.password"
      />

      <BaseInput
        id="password_confirmation"
        v-model="form.password_confirmation"
        label="Confirm password"
        type="password"
        autocomplete="new-password"
        required
      />

      <BaseButton type="submit" variant="primary" :loading="isSubmitting" class="w-full">
        Register
      </BaseButton>
    </form>

    <p class="mt-4 text-sm text-gray-600">
      Already have an account?
      <NuxtLink to="/login" class="font-medium text-blue-600 hover:underline">
        Log in
      </NuxtLink>
    </p>
  </section>
</template>
