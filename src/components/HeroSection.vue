<script setup>
import { useAuthNavigation } from "../composables/useAuthNavigation";
import { authStore } from "../stores/auth";

const { goTo, scrollToSection } = useAuthNavigation();

function buildYourLook() {
  if (authStore.isLoggedIn) {
    goTo({ path: "/recommendations", query: { occasion: "casual" } });
  } else {
    goTo("/signup");
  }
}

function openFaceInsights() {
  if (authStore.isLoggedIn) {
    goTo("/face-insights");
  } else {
    goTo("/signup");
  }
}
</script>

<template>
  <section id="home" class="relative overflow-hidden pb-16 pt-14 md:pb-24 md:pt-20">
    <div class="pointer-events-none absolute inset-0 bg-hero-mesh" />
    <div class="pointer-events-none absolute -right-24 top-0 h-[28rem] w-[28rem] rounded-full bg-brand-rose/10 blur-3xl" />
    <div class="pointer-events-none absolute -bottom-28 left-0 h-80 w-80 rounded-full bg-brand-plum/10 blur-3xl" />

    <div class="container-shell relative grid items-center gap-12 lg:grid-cols-[1.2fr_0.8fr] lg:gap-16">
      <div>
        <p class="font-display animate-fade-in stagger-1 text-3xl font-semibold tracking-tight text-brand-plum md:text-4xl lg:text-5xl">
          MakemeupAI
        </p>
        <h1 class="mt-4 animate-fade-in stagger-2 text-3xl font-semibold leading-[1.15] text-brand-ink md:text-5xl lg:text-[3.25rem]">
          Style that feels like you.
        </h1>
        <p class="mt-5 max-w-lg animate-fade-in stagger-3 text-base leading-relaxed text-brand-muted md:text-lg">
          Face insights, wardrobe looks, and Lahore salon booking — one calm place to plan your next glow.
        </p>
        <div class="mt-8 flex flex-wrap gap-3 opacity-0 animate-fade-in stagger-3" style="animation-fill-mode: forwards; animation-delay: 0.32s">
          <button type="button" class="btn-primary px-6 py-3 text-base" @click="buildYourLook">
            Build your look
          </button>
          <button type="button" class="btn-ghost px-6 py-3 text-base" @click="scrollToSection('features')">
            See how it works
          </button>
        </div>
      </div>

      <div
        class="relative animate-fade-in-slow opacity-0"
        style="animation-fill-mode: forwards; animation-delay: 0.2s"
      >
        <div class="animate-float rounded-3xl border border-brand-line/80 bg-white/70 p-6 shadow-elevated backdrop-blur-sm md:p-7">
          <p class="font-display text-lg font-semibold text-brand-plum">Tonight’s look</p>
          <p class="mt-1 text-sm text-brand-muted">Wedding · elegant soft glam</p>
          <ul class="mt-5 space-y-3 border-t border-brand-line/80 pt-5 text-sm text-brand-ink">
            <li class="flex justify-between gap-4">
              <span class="text-brand-muted">Hair</span>
              <span class="font-medium">Textured low bun</span>
            </li>
            <li class="flex justify-between gap-4">
              <span class="text-brand-muted">Finish</span>
              <span class="font-medium">Soft peach glow</span>
            </li>
            <li class="flex justify-between gap-4">
              <span class="text-brand-muted">Detail</span>
              <span class="font-medium">Minimal Arabic mehndi</span>
            </li>
          </ul>
          <button type="button" class="btn-secondary mt-6 w-full" @click="openFaceInsights">
            Start with a selfie
          </button>
        </div>
      </div>
    </div>
  </section>
</template>
