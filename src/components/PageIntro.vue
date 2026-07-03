<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: "",
  },
  description: {
    type: String,
    default: "",
  },
});
</script>

<template>
  <section class="border-b border-brand-border bg-white/60 py-14 backdrop-blur-sm">
    <div class="container-shell max-w-3xl">
      <h1 class="section-title gradient-text">{{ title }}</h1>
      <p v-if="subtitle" class="mt-3 text-lg font-semibold text-brand-ink">{{ subtitle }}</p>
      <p v-if="description" class="mt-4 leading-relaxed text-brand-muted">{{ description }}</p>
    </div>
  </section>
</template>
