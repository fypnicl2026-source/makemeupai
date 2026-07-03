<script setup>
import { useAuthNavigation } from "../composables/useAuthNavigation";
import { authStore } from "../stores/auth";

const { goTo, goToProtected, scrollToSection } = useAuthNavigation();

function buildYourLook() {
  if (authStore.isLoggedIn) {
    goTo({ path: "/recommendations", query: { occasion: "casual" } });
  } else {
    goTo("/signup");
  }
}

function generateCompleteLook() {
  if (authStore.isLoggedIn) {
    goTo({ path: "/recommendations", query: { occasion: "formal" } });
  } else {
    goTo("/signup");
  }
}
</script>

<template>
  <section id="home" class="relative overflow-hidden py-20 md:py-28">
    <div class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full bg-brand-rose/10 blur-3xl" />
    <div class="pointer-events-none absolute -bottom-20 -left-20 h-72 w-72 rounded-full bg-brand-lilac/10 blur-3xl" />

    <div class="container-shell relative grid items-center gap-12 md:grid-cols-[1.15fr_0.85fr]">
      <div class="animate-fade-in">
        <p class="eyebrow mb-5">
          <span class="h-1.5 w-1.5 rounded-full bg-brand-rose" />
          AI-Inspired Style Companion
        </p>
        <h1 class="mb-6 text-4xl font-extrabold leading-tight md:text-6xl lg:text-7xl">
          Style Smarter.
          <span class="gradient-text">Glow Better.</span>
        </h1>
        <p class="max-w-xl text-lg leading-relaxed text-brand-muted">
          Plan outfits from your digital wardrobe, explore AI-inspired beauty and styling ideas,
          and book verified beauticians across Pakistan — all in one polished platform.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
          <button type="button" class="btn-primary px-6 py-3 text-base" @click="buildYourLook">
            Build Your Look
          </button>
          <button type="button" class="btn-ghost px-6 py-3 text-base" @click="scrollToSection('features')">
            Explore Features
          </button>
        </div>
        <div class="mt-10 flex flex-wrap gap-6 text-sm text-brand-muted">
          <span class="flex items-center gap-2">
            <span class="text-brand-rose">✦</span> AI face insights
          </span>
          <span class="flex items-center gap-2">
            <span class="text-brand-lilac">✦</span> Verified beauticians
          </span>
          <span class="flex items-center gap-2">
            <span class="text-brand-plum">✦</span> Smart wardrobe
          </span>
        </div>
      </div>

      <div class="glass-card animate-float p-6 shadow-elevated md:p-8">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-lg font-bold text-brand-plum">Your Look Snapshot</h3>
          <span class="rounded-full bg-brand-blush-deep px-2.5 py-0.5 text-xs font-semibold text-brand-plum">
            AI Preview
          </span>
        </div>
        <div class="space-y-3 rounded-xl bg-brand-blush-deep/60 p-4">
          <p class="text-sm text-brand-muted"><span class="font-semibold text-brand-ink">Event:</span> Wedding / Bridal</p>
          <p class="text-sm text-brand-muted"><span class="font-semibold text-brand-ink">Mood:</span> Elegant Soft Glam</p>
          <p class="text-sm text-brand-muted"><span class="font-semibold text-brand-ink">Hair:</span> Textured Low Bun</p>
          <p class="text-sm text-brand-muted"><span class="font-semibold text-brand-ink">Mehndi:</span> Minimal Arabic</p>
        </div>
        <button type="button" class="btn-secondary mt-5 w-full" @click="generateCompleteLook">
          Generate My Complete Look
        </button>
      </div>
    </div>
  </section>
</template>
