<script setup>
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { showToast } from "../composables/useToast";
import { getBeauticians } from "../services/beauticians";
import { authStore } from "../stores/auth";

const router = useRouter();

const beauticians = ref([]);
const loading = ref(false);
const fetchError = ref("");

const previewBeauticians = computed(() => beauticians.value.slice(0, 3));

function badgeClass(badge) {
  const classes = {
    beginner: "bg-gray-100 text-gray-600",
    intermediate: "bg-blue-100 text-blue-700",
    expert: "bg-amber-100 text-amber-800",
  };
  return classes[badge] || classes.beginner;
}

function renderStars(rating) {
  const full = Math.floor(Number(rating));
  return "★".repeat(full) + "☆".repeat(5 - full);
}

function formatSpecializations(specs) {
  if (!specs?.length) return "—";
  return specs.map((s) => s.charAt(0).toUpperCase() + s.slice(1)).join(", ");
}

async function fetchBeauticians() {
  loading.value = true;
  fetchError.value = "";
  try {
    beauticians.value = await getBeauticians();
  } catch {
    beauticians.value = [];
    fetchError.value = "Could not load beauticians. Please try again.";
  } finally {
    loading.value = false;
  }
}

function viewProfile() {
  router.push("/beauticians");
}

function handleBookNow() {
  if (!authStore.isLoggedIn) {
    showToast("Please sign in to book", "error");
    setTimeout(() => router.push("/signin"), 1500);
    return;
  }
  router.push("/beauticians");
}

onMounted(() => {
  fetchBeauticians();
});
</script>

<template>
  <section id="beauticians" class="py-20">
    <div class="container-shell">
      <p class="eyebrow mb-4">Professionals</p>
      <h2 class="section-title mb-10">
        Find the Right <span class="gradient-text">Beautician</span> for Your Look
      </h2>

      <div v-if="loading" class="flex justify-center py-16">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-brand-border border-t-brand-rose" />
      </div>

      <div v-else-if="fetchError" class="glass-card px-6 py-14 text-center">
        <p class="font-bold text-brand-plum">{{ fetchError }}</p>
        <button type="button" class="btn-primary mt-6" @click="fetchBeauticians">Try Again</button>
      </div>

      <template v-else>
        <div v-if="previewBeauticians.length === 0" class="glass-card px-6 py-14 text-center">
          <p class="font-bold text-brand-plum">No beauticians available yet</p>
          <p class="mt-2 text-sm text-brand-muted">Check back soon or browse the full directory.</p>
        </div>

        <div v-else class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
          <article v-for="b in previewBeauticians" :key="b.id" class="glass-card card-hover p-6">
            <div class="mb-3 flex items-start justify-between gap-2">
              <h3 class="text-lg font-bold text-brand-ink">{{ b.name }}</h3>
              <span
                class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-bold capitalize"
                :class="badgeClass(b.skill_badge)"
              >
                {{ b.skill_badge }}
              </span>
            </div>
            <div class="space-y-1.5 text-sm text-brand-muted">
              <p><strong class="text-brand-ink">Specialties:</strong> {{ formatSpecializations(b.specializations) }}</p>
              <p>
                <strong class="text-brand-ink">Rating:</strong>
                <span class="text-amber-500">{{ renderStars(b.avg_rating) }}</span>
                ({{ b.avg_rating }})
              </p>
              <p><strong class="text-brand-ink">Rate:</strong> Rs. {{ Number(b.hourly_rate).toLocaleString() }} / hr</p>
              <p v-if="b.city"><strong class="text-brand-ink">City:</strong> {{ b.city }}</p>
            </div>
            <div class="mt-5 flex gap-2">
              <button type="button" class="btn-ghost flex-1 text-sm" @click="viewProfile">View Profile</button>
              <button type="button" class="btn-primary flex-1 text-sm" @click="handleBookNow">Book Now</button>
            </div>
          </article>
        </div>

        <div class="mt-10 text-center">
          <RouterLink
            to="/beauticians"
            class="text-sm font-bold text-brand-plum transition-colors hover:text-brand-rose"
          >
            View all beauticians →
          </RouterLink>
        </div>
      </template>
    </div>
  </section>
</template>
