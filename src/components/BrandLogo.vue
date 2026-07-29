<script setup>
defineProps({
  size: {
    type: String,
    default: "md",
    validator: (v) => ["sm", "md", "lg"].includes(v),
  },
  showText: {
    type: Boolean,
    default: true,
  },
  to: {
    type: String,
    default: "/",
  },
});

const sizeMap = {
  sm: { icon: "h-7 w-7", text: "text-base" },
  md: { icon: "h-9 w-9", text: "text-lg" },
  lg: { icon: "h-11 w-11", text: "text-xl" },
};
</script>

<template>
  <RouterLink :to="to" class="group inline-flex items-center gap-2.5">
    <img
      src="/logo.svg"
      alt="MakemeupAI"
      :class="[sizeMap[size].icon, 'shrink-0 transition-transform duration-300 group-hover:scale-105']"
    />
    <span
      v-if="showText"
      :class="[
        sizeMap[size].text,
        'font-display font-semibold tracking-tight text-brand-plum transition-colors group-hover:text-brand-rose',
      ]"
    >
      MakemeupAI
    </span>
  </RouterLink>
</template>
