export default defineNuxtConfig({
  compatibilityDate: '2026-06-01',

  devtools: { enabled: true },

  modules: ['@nuxtjs/tailwindcss'],

  typescript: {
    strict: true,
  },

  runtimeConfig: {
    public: {
      // Override with NUXT_PUBLIC_API_BASE at runtime.
      apiBase: 'http://localhost:8000/api',
    },
  },

  app: {
    head: {
      title: 'Creator Analytics',
      meta: [
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        {
          name: 'description',
          content: 'Unified analytics dashboard for content creators.',
        },
      ],
    },
  },
})
