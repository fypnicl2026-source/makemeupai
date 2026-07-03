<script setup>
import { useAuthNavigation } from "../composables/useAuthNavigation";
import { authStore } from "../stores/auth";

defineProps({
  features: {
    type: Array,
    required: true,
  },
  showIntro: {
    type: Boolean,
    default: true,
  },
});

const { goTo, goToProtected } = useAuthNavigation();

function handleFeatureClick(feature) {
  if (feature.requiresAuth && !authStore.isLoggedIn) {
    goToProtected(feature.route);
    return;
  }
  goTo(feature.route);
}
</script>

<template>
  <section id="features" class="py-20">
    <div class="container-shell">
      <p v-if="showIntro" class="eyebrow mb-4">Platform Features</p>
      <h2 :class="['section-title', showIntro ? 'mb-3' : 'mb-6']">
        Everything You Need for a <span class="gradient-text">Complete Look</span>
      </h2>
      <p v-if="showIntro" class="section-subtitle mb-10">
        From your digital wardrobe to beautician booking — explore AI-inspired tools built for
        everyday styling and special occasions in Pakistan.
      </p>
      <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        <article
          v-for="feature in features"
          :key="feature.title"
          class="glass-card card-hover group cursor-pointer p-6"
          role="button"
          tabindex="0"
          @click="handleFeatureClick(feature)"
          @keydown.enter="handleFeatureClick(feature)"
        >
          <div
            class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-blush-deep to-white text-brand-rose shadow-soft transition-transform group-hover:scale-110"
          >
            ✦
          </div>
          <h3 class="text-lg font-bold text-brand-ink">{{ feature.title }}</h3>
          <p class="mt-2 text-sm leading-relaxed text-brand-muted">{{ feature.description }}</p>
          <p class="mt-4 text-xs font-bold uppercase tracking-wider text-brand-plum opacity-0 transition-opacity group-hover:opacity-100">
            Explore →
          </p>
        </article>
      </div>
    </div>
  </section>
</template>
