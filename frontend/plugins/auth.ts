/**
 * Restore the authenticated user on app start (server and client)
 * so pages and middleware can rely on auth state being initialized.
 */
export default defineNuxtPlugin(async () => {
  await useAuthStore().fetchUser()
})
