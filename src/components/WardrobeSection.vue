<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useAuthNavigation } from "../composables/useAuthNavigation";
import { getItems } from "../services/wardrobe";
import { authStore } from "../stores/auth";

const { goTo, goToProtected } = useAuthNavigation();

const loading = ref(false);
const fetchError = ref("");
const wardrobeItems = ref([]);

const categoryBreakdown = computed(() => {
  const counts = {};
  for (const item of wardrobeItems.value) {
    counts[item.category] = (counts[item.category] || 0) + 1;
  }
  return Object.entries(counts)
    .map(([category, count]) => `${category.charAt(0).toUpperCase() + category.slice(1)}: ${count}`)
    .join(", ");
});

const isEmpty = computed(() => wardrobeItems.value.length === 0);

async function fetchWardrobe() {
  if (!authStore.isLoggedIn) {
    wardrobeItems.value = [];
    fetchError.value = "";
    return;
  }

  loading.value = true;
  fetchError.value = "";
  try {
    wardrobeItems.value = await getItems();
  } catch {
    wardrobeItems.value = [];
    fetchError.value = "Could not load your wardrobe. Please try again.";
  } finally {
    loading.value = false;
  }
}

watch(
  () => authStore.isLoggedIn,
  () => {
    fetchWardrobe();
  }
);

onMounted(() => {
  fetchWardrobe();
});
</script>

<template>
  <section id="wardrobe" class="border-y border-brand-border bg-white/70 py-20 backdrop-blur-sm">
    <div class="container-shell">
      <p class="eyebrow mb-4">Wardrobe</p>
      <h2 class="section-title mb-10">Your Smart <span class="gradient-text">Digital Wardrobe</span></h2>

      <div v-if="!authStore.isLoggedIn" class="glass-card p-8 text-center md:text-left">
        <p class="text-lg font-bold text-brand-ink">Your wardrobe is empty</p>
        <p class="mt-2 text-sm text-brand-muted">
          Create an account to upload clothes and get personalized outfit suggestions.
        </p>
        <div class="mt-6 flex flex-wrap justify-center gap-3 md:justify-start">
          <button type="button" class="btn-primary" @click="goTo('/signup')">Get Started</button>
          <button type="button" class="btn-ghost" @click="goTo('/signin')">Sign In</button>
        </div>
      </div>

      <div v-else-if="loading" class="flex justify-center py-16">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-brand-border border-t-brand-rose" />
      </div>

      <div v-else-if="fetchError" class="glass-card px-6 py-10 text-center">
        <p class="font-bold text-brand-plum">{{ fetchError }}</p>
        <button type="button" class="btn-primary mt-4" @click="fetchWardrobe">Try Again</button>
      </div>

      <div v-else class="glass-card p-8">
        <template v-if="isEmpty">
          <p class="text-lg font-bold text-brand-ink">Your wardrobe is empty</p>
          <p class="mt-2 text-sm text-brand-muted">
            Add your first item to start getting outfit suggestions.
          </p>
        </template>
        <template v-else>
          <p class="text-lg font-bold text-brand-ink">Wardrobe snapshot</p>
          <p class="mt-2 text-sm text-brand-muted">
            <span class="font-semibold text-brand-ink">{{ wardrobeItems.length }} items</span>
            <span v-if="categoryBreakdown"> — {{ categoryBreakdown }}</span>
          </p>
        </template>
        <div class="mt-6 flex flex-wrap gap-3">
          <button type="button" class="btn-primary" @click="goToProtected('/wardrobe')">
            Manage Wardrobe
          </button>
          <button
            type="button"
            class="btn-ghost"
            @click="goToProtected('/recommendations?occasion=casual')"
          >
            Get Outfit Ideas
          </button>
        </div>
      </div>
    </div>
  </section>
</template>
