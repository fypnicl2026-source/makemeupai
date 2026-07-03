<script setup>
import { useAuthNavigation } from "../composables/useAuthNavigation";
import {
  howItWorksCompactIntro,
  howItWorksCta,
  howItWorksFaq,
  howItWorksGettingStarted,
} from "../data/howItWorksContent";

defineProps({
  steps: {
    type: Array,
    required: true,
  },
  variant: {
    type: String,
    default: "compact",
    validator: (v) => ["compact", "full"].includes(v),
  },
});

const { goTo } = useAuthNavigation();

function stepKey(step, index) {
  return step.title || step.summary || index;
}
</script>

<template>
  <section id="how" class="border-y border-brand-line bg-white/70 py-20 backdrop-blur-sm">
    <div class="container-shell">
      <p class="eyebrow mb-4">How It Works</p>
      <h2 class="section-title mb-3">Create Your Look in 4 Simple Steps</h2>
      <p class="section-subtitle mb-10">{{ howItWorksCompactIntro }}</p>

      <div v-if="variant === 'compact'" class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
        <article
          v-for="(step, index) in steps"
          :key="stepKey(step, index)"
          class="glass-card card-hover relative overflow-hidden p-5"
        >
          <div
            class="mb-4 inline-flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-brand-rose to-brand-lilac text-sm font-bold text-white shadow-soft"
          >
            {{ index + 1 }}
          </div>
          <h3 class="mb-2 font-bold text-brand-ink">{{ step.title }}</h3>
          <p class="text-sm leading-relaxed text-brand-muted">{{ step.summary }}</p>
        </article>
      </div>

      <div v-else class="max-w-3xl space-y-5">
        <article
          v-for="(step, index) in steps"
          :key="stepKey(step, index)"
          class="glass-card card-hover flex gap-5 p-6"
        >
          <div
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-rose to-brand-lilac font-bold text-white shadow-soft"
          >
            {{ index + 1 }}
          </div>
          <div>
            <h3 class="text-lg font-bold text-brand-ink">{{ step.title }}</h3>
            <p class="mt-2 text-sm leading-relaxed text-brand-muted">{{ step.detail }}</p>
            <p v-if="step.tip" class="mt-3 rounded-lg bg-brand-blush-deep px-3 py-2 text-xs text-brand-plum">
              <span class="font-bold">Tip:</span> {{ step.tip }}
            </p>
          </div>
        </article>
      </div>

      <p v-if="variant === 'compact'" class="mt-8 text-center">
        <RouterLink
          to="/how-it-works"
          class="text-sm font-bold text-brand-plum transition-colors hover:text-brand-rose"
        >
          See full guide →
        </RouterLink>
      </p>

      <template v-if="variant === 'full'">
        <div class="mt-12 glass-card p-6">
          <h2 class="text-xl font-bold text-brand-plum">What you need to get started</h2>
          <ul class="mt-4 list-inside list-disc space-y-2 text-sm text-brand-muted">
            <li v-for="item in howItWorksGettingStarted" :key="item">{{ item }}</li>
          </ul>
        </div>

        <div class="mt-10">
          <h2 class="mb-5 text-2xl font-bold text-brand-plum">Common questions</h2>
          <div class="space-y-3">
            <details
              v-for="item in howItWorksFaq"
              :key="item.question"
              class="glass-card group p-5"
            >
              <summary class="cursor-pointer list-none font-bold text-brand-ink [&::-webkit-details-marker]:hidden">
                <h3 class="inline text-base">{{ item.question }}</h3>
              </summary>
              <p class="mt-3 text-sm leading-relaxed text-brand-muted">{{ item.answer }}</p>
            </details>
          </div>
        </div>

        <div class="mt-12 text-center">
          <p class="mb-5 text-brand-muted">{{ howItWorksCta.line }}</p>
          <div class="flex flex-wrap justify-center gap-3">
            <button type="button" class="btn-primary" @click="goTo(howItWorksCta.primaryRoute)">
              {{ howItWorksCta.primaryLabel }}
            </button>
            <button type="button" class="btn-ghost" @click="goTo(howItWorksCta.secondaryRoute)">
              {{ howItWorksCta.secondaryLabel }}
            </button>
          </div>
        </div>
      </template>

      <div v-if="variant === 'compact'" class="mt-10 text-center">
        <button type="button" class="btn-primary px-8" @click="goTo('/signup')">Get Started</button>
      </div>
    </div>
  </section>
</template>
