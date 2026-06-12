export default defineNuxtRouteMiddleware(() => {
  if (useAuthStore().isAuthenticated) {
    return navigateTo('/account')
  }
})
