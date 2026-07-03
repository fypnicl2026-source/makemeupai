<script setup>
import LandingLayout from "../layouts/LandingLayout.vue";
import BrandLogo from "../components/BrandLogo.vue";
</script>

<template>
  <LandingLayout>
    <section class="flex min-h-[60vh] items-center py-20">
      <div class="container-shell">
        <div class="glass-card mx-auto max-w-lg p-10 text-center shadow-elevated">
          <div class="mb-6 flex justify-center">
            <BrandLogo size="lg" :show-text="false" to="/" />
          </div>
          <p class="eyebrow mb-4">404</p>
          <h1 class="text-3xl font-bold text-brand-ink">Page not found</h1>
          <p class="mb-8 mt-3 text-brand-muted">
            The page you are looking for does not exist or may have been moved.
          </p>
          <RouterLink to="/" class="btn-primary inline-block px-8 py-3">Go back home</RouterLink>
        </div>
      </div>
    </section>
  </LandingLayout>
</template>
