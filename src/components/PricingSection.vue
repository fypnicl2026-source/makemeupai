<script setup>
import { showToast } from "../composables/useToast";
import { useAuthNavigation } from "../composables/useAuthNavigation";
import { authStore } from "../stores/auth";

defineProps({
  plans: {
    type: Array,
    required: true,
  },
});

const { goTo } = useAuthNavigation();

function handleStartFree() {
  goTo("/signup");
}

function handleGoPremium() {
  if (authStore.isLoggedIn) {
    showToast("Premium plans are coming soon. Enjoy all free features for now!", "success");
    goTo("/dashboard");
  } else {
    goTo("/signup");
  }
}
</script>

<template>
  <section id="pricing" class="py-20">
    <div class="container-shell">
      <p class="eyebrow mb-4">Pricing</p>
      <h2 class="section-title mb-10">Choose Your <span class="gradient-text">Styling Plan</span></h2>
      <div class="grid gap-6 md:grid-cols-2">
        <article
          v-for="plan in plans"
          :key="plan.name"
          class="glass-card card-hover relative overflow-hidden p-8"
          :class="{ 'ring-2 ring-brand-rose/30 shadow-glow-sm': plan.featured }"
        >
          <div
            v-if="plan.featured"
            class="absolute right-4 top-4 rounded-full bg-gradient-to-r from-brand-rose to-brand-lilac px-3 py-0.5 text-xs font-bold text-white"
          >
            Popular
          </div>
          <h3 class="text-2xl font-bold text-brand-ink">{{ plan.name }}</h3>
          <p class="mb-6 mt-2 text-2xl font-extrabold text-brand-plum">{{ plan.price }}</p>
          <button
            v-if="plan.featured"
            type="button"
            class="btn-primary w-full"
            @click="handleGoPremium"
          >
            {{ plan.cta }}
          </button>
          <button v-else type="button" class="btn-ghost w-full" @click="handleStartFree">
            {{ plan.cta }}
          </button>
        </article>
      </div>
    </div>
  </section>
</template>
