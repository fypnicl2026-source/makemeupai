<script setup>
import { computed, onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import { getRecommendations } from "../services/recommendations";
import { authStore } from "../stores/auth";

const route = useRoute();
const VALID_OCCASIONS = ["casual", "work", "formal", "party"];

const OCCASIONS = [
  { label: "Casual", value: "casual" },
  { label: "Work", value: "work" },
  { label: "Formal", value: "formal" },
  { label: "Party", value: "party" },
];

const COLOR_MAP = {
  red: "#ef4444",
  black: "#1f1124",
  white: "#ffffff",
  blue: "#3b82f6",
  navy: "#1e3a5f",
  green: "#22c55e",
  pink: "#ec4899",
  purple: "#a855f7",
  yellow: "#eab308",
  orange: "#f97316",
  brown: "#92400e",
  beige: "#d4b896",
  gray: "#9ca3af",
  grey: "#9ca3af",
  cream: "#fffdd0",
  gold: "#d4af37",
  silver: "#c0c0c0",
};

const WEATHER_EMOJI = {
  clear: "☀️",
  clouds: "☁️",
  rain: "🌧️",
  drizzle: "🌧️",
  thunderstorm: "⛈️",
  snow: "❄️",
  mist: "🌫️",
  fog: "🌫️",
};

const selectedOccasion = ref("casual");
const combinations = ref([]);
const loading = ref(false);
const hasFetched = ref(false);
const error = ref("");

const weatherBadge = computed(() => {
  if (combinations.value.length === 0) return null;

  const weather = combinations.value[0].weather;
  const emoji = WEATHER_EMOJI[weather.condition] || "🌤️";
  const city = authStore.user?.city ?? "your city";

  return `${emoji} ${weather.temp}°C — ${weather.description} in ${city}`;
});

function colorToCss(colorName) {
  if (!colorName) return "#ca4d91";
  return COLOR_MAP[colorName.toLowerCase().trim()] || "#ca4d91";
}

function normalizeItems(items) {
  if (Array.isArray(items)) return items;
  return items?.data ?? [];
}

async function fetchRecommendations() {
  hasFetched.value = true;
  loading.value = true;
  error.value = "";
  combinations.value = [];

  try {
    combinations.value = await getRecommendations(selectedOccasion.value);
  } catch (err) {
    error.value =
      err.response?.data?.message || "Could not load recommendations. Please try again.";
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  if (route.query.occasion && VALID_OCCASIONS.includes(route.query.occasion)) {
    selectedOccasion.value = route.query.occasion;
  }
});
</script>

<template>
  <DashboardLayout>
    <section class="container-shell py-10">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-brand-plum">Outfit Recommendations</h1>
        <p class="mt-1 text-sm text-[#6f6176]">
          Get personalized outfit suggestions based on your wardrobe and local weather.
        </p>
      </div>

      <div class="mb-6 flex flex-wrap gap-2">
        <button
          v-for="occasion in OCCASIONS"
          :key="occasion.value"
          type="button"
          class="rounded-full px-4 py-2 text-sm transition-colors"
          :class="
            selectedOccasion === occasion.value
              ? 'bg-[#fff0f5] font-semibold text-brand-plum ring-1 ring-brand-rose'
              : 'bg-white text-[#6f6176] hover:text-brand-plum'
          "
          @click="selectedOccasion = occasion.value"
        >
          {{ occasion.label }}
        </button>
      </div>

      <button type="button" class="btn-primary mb-8" :disabled="loading" @click="fetchRecommendations">
        {{ loading ? "Finding..." : "Get My Outfit" }}
      </button>

      <div v-if="!hasFetched" class="glass-card flex flex-col items-center px-6 py-16 text-center">
        <svg
          class="mb-6 h-24 w-24 text-brand-plum/40"
          viewBox="0 0 120 120"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
          aria-hidden="true"
        >
          <path
            d="M60 15 L75 35 H45 Z"
            stroke="currentColor"
            stroke-width="2"
            fill="#fff0f5"
          />
          <path
            d="M30 35 H90 L85 95 H35 Z"
            stroke="currentColor"
            stroke-width="2"
            fill="#fff0f5"
          />
          <path d="M60 35 V95" stroke="#ca4d91" stroke-width="1.5" stroke-dasharray="4 4" />
          <circle cx="60" cy="12" r="4" stroke="currentColor" stroke-width="2" fill="white" />
        </svg>
        <p class="text-lg font-semibold text-brand-plum">Ready for your next look?</p>
        <p class="mt-2 text-sm text-[#6f6176]">Select an occasion and click Get My Outfit</p>
      </div>

      <div v-else-if="loading" class="flex flex-col items-center py-20">
        <div
          class="h-10 w-10 animate-spin rounded-full border-4 border-[#f0dce8] border-t-brand-rose"
        ></div>
        <p class="mt-4 text-sm text-[#6f6176]">Finding your perfect outfit...</p>
      </div>

      <div v-else-if="error" class="glass-card px-6 py-12 text-center">
        <p class="font-semibold text-brand-plum">{{ error }}</p>
        <button type="button" class="btn-primary mt-4" @click="fetchRecommendations">Try Again</button>
      </div>

      <div v-else-if="combinations.length === 0" class="glass-card px-6 py-12 text-center">
        <p class="font-semibold text-brand-plum">Add at least 3 items to your wardrobe first</p>
        <p class="mt-2 text-sm text-[#6f6176]">
          We need more clothing items to build outfit combinations for you.
        </p>
        <RouterLink to="/wardrobe" class="btn-primary mt-6 inline-block">Go to Wardrobe</RouterLink>
      </div>

      <div v-else>
        <div
          v-if="weatherBadge"
          class="mb-6 inline-flex rounded-full bg-[#fff0f5] px-4 py-2 text-sm font-medium text-brand-plum ring-1 ring-[#f0dce8]"
        >
          {{ weatherBadge }}
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <article
            v-for="(combo, index) in combinations"
            :key="index"
            class="glass-card p-5"
          >
            <h2 class="mb-4 text-lg font-semibold text-brand-plum">Outfit {{ index + 1 }}</h2>
            <div class="flex flex-wrap gap-3">
              <div
                v-for="item in normalizeItems(combo.items)"
                :key="item.id"
                class="w-20 text-center"
              >
                <div class="aspect-square overflow-hidden rounded-xl border border-[#f0dce8]">
                  <img
                    v-if="item.image_url"
                    :src="item.image_url"
                    :alt="item.name"
                    class="h-full w-full object-cover"
                  />
                  <div
                    v-else
                    class="flex h-full w-full items-center justify-center p-1"
                    :style="{ backgroundColor: colorToCss(item.colors?.[0]) }"
                  >
                    <span class="line-clamp-3 text-[10px] font-medium leading-tight text-white drop-shadow">
                      {{ item.name }}
                    </span>
                  </div>
                </div>
                <p class="mt-1 text-xs capitalize text-[#6f6176]">{{ item.category }}</p>
              </div>
            </div>
          </article>
        </div>
      </div>
    </section>
  </DashboardLayout>
</template>
