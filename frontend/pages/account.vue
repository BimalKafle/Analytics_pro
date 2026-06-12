<script setup lang="ts">
import { storeToRefs } from 'pinia'

definePageMeta({ middleware: 'auth' })

const authStore = useAuthStore()
const { user } = storeToRefs(authStore)
const { logout, resendVerificationEmail, refreshUser } = authStore

const verificationNotice = ref<string | null>(null)
const isResending = ref(false)

async function resend(): Promise<void> {
  isResending.value = true

  try {
    verificationNotice.value = await resendVerificationEmail()
  } catch {
    verificationNotice.value = 'Could not send the email. Please try again later.'
  } finally {
    isResending.value = false
  }
}

async function checkVerification(): Promise<void> {
  await refreshUser()
}

async function signOut(): Promise<void> {
  await logout()
  await navigateTo('/login')
}
</script>

<template>
  <section v-if="user" class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold">Account</h1>
      <BaseButton variant="secondary" @click="signOut">
        Log out
      </BaseButton>
    </div>

    <BaseAlert v-if="!user.email_verified" variant="info">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <span>Your email address is not verified yet. Please check your inbox.</span>
        <span class="flex gap-2">
          <BaseButton variant="secondary" :loading="isResending" @click="resend">
            Resend email
          </BaseButton>
          <BaseButton variant="secondary" @click="checkVerification">
            I verified
          </BaseButton>
        </span>
      </div>
      <p v-if="verificationNotice" class="mt-2">{{ verificationNotice }}</p>
    </BaseAlert>

    <dl class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white">
      <div class="flex justify-between px-4 py-3 text-sm">
        <dt class="font-medium text-gray-600">Name</dt>
        <dd>{{ user.name }}</dd>
      </div>
      <div class="flex justify-between px-4 py-3 text-sm">
        <dt class="font-medium text-gray-600">Email</dt>
        <dd>{{ user.email }}</dd>
      </div>
      <div class="flex justify-between px-4 py-3 text-sm">
        <dt class="font-medium text-gray-600">Email verified</dt>
        <dd>{{ user.email_verified ? 'Yes' : 'No' }}</dd>
      </div>
    </dl>

    <PlatformConnections v-if="user.email_verified" />
    <p v-else class="text-sm text-gray-600">
      Verify your email address to connect platforms.
    </p>
  </section>
</template>
