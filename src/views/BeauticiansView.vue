<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { showToast } from "../composables/useToast";
import BookingModal from "../components/BookingModal.vue";
import LandingLayout from "../layouts/LandingLayout.vue";
import { getBeauticians } from "../services/beauticians";
import { authStore } from "../stores/auth";

const router = useRouter();

const CITIES = ["Lahore"];
const GENDER_FILTERS = [
  { label: "All", value: "" },
  { label: "Women", value: "female" },
  { label: "Men", value: "male" },
];
const SPECIALIZATIONS = [
  "",
  "makeup",
  "bridal",
  "hairstyle",
  "skincare",
  "mehndi",
  "haircut",
  "beard",
  "grooming",
  "groom",
  "styling",
  "party",
];

const beauticians = ref([]);
const loading = ref(false);
const fetchError = ref("");
const selectedCity = ref("Lahore");
const selectedGenderFocus = ref(
  authStore.user?.gender === "male" || authStore.user?.gender === "female"
    ? authStore.user.gender
    : ""
);
const selectedSpecialization = ref("");
const selectedBeautician = ref(null);
const showModal = ref(false);

function badgeClass(badge) {
  const classes = {
    beginner: "bg-gray-100 text-gray-600",
    intermediate: "bg-blue-100 text-blue-700",
    expert: "bg-amber-100 text-amber-800",
  };
  return classes[badge] || classes.beginner;
}

function genderLabel(focus) {
  if (focus === "male") return "Men’s salon";
  if (focus === "female") return "Women’s salon";
  return "Unisex";
}

function initials(name) {
  return name
    .split(" ")
    .map((part) => part[0])
    .join("")
    .slice(0, 2)
    .toUpperCase();
}

function renderStars(rating) {
  const full = Math.floor(Number(rating));
  return "★".repeat(full) + "☆".repeat(5 - full);
}

async function fetchBeauticians() {
  loading.value = true;
  fetchError.value = "";
  try {
    const filters = { city: selectedCity.value || "Lahore" };
    if (selectedSpecialization.value) filters.specialization = selectedSpecialization.value;
    if (selectedGenderFocus.value) filters.gender_focus = selectedGenderFocus.value;
    beauticians.value = await getBeauticians(filters);
  } catch {
    beauticians.value = [];
    fetchError.value = "Could not load salons. Please try again.";
  } finally {
    loading.value = false;
  }
}

function handleBookNow(beautician) {
  if (!authStore.isLoggedIn) {
    showToast("Please sign in to book", "error");
    setTimeout(() => router.push("/signin"), 1500);
    return;
  }

  selectedBeautician.value = beautician;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  selectedBeautician.value = null;
}

function onBookingSuccess() {
  showToast("Booking confirmed!", "success");
}

const emptyHint = computed(() => {
  if (selectedGenderFocus.value === "male") {
    return "No men’s salons match these filters in Lahore. Clear specialization or show All.";
  }
  if (selectedGenderFocus.value === "female") {
    return "No women’s salons match these filters in Lahore. Clear specialization or show All.";
  }
  return "Try adjusting your filters — Lahore has preset men’s and women’s salons.";
});

watch([selectedCity, selectedSpecialization, selectedGenderFocus], () => {
  fetchBeauticians();
});

onMounted(() => {
  fetchBeauticians();
});
</script>

<template>
  <LandingLayout>
    <section class="container-shell py-10">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-brand-plum">Book a Lahore salon</h1>
        <p class="mt-1 text-sm text-brand-muted">
          Preset men’s grooming and women’s beauty salons across Lahore — filter by gender, then book.
        </p>
      </div>

      <div class="mb-6">
        <p class="mb-2 text-sm font-medium text-brand-muted">Salon type</p>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="g in GENDER_FILTERS"
            :key="g.label"
            type="button"
            class="rounded-full px-4 py-2 text-sm transition-colors"
            :class="
              selectedGenderFocus === g.value
                ? 'bg-[#fff0f5] font-semibold text-brand-plum ring-1 ring-brand-rose'
                : 'bg-white text-brand-muted hover:text-brand-plum'
            "
            @click="selectedGenderFocus = g.value"
          >
            {{ g.label }}
          </button>
        </div>
      </div>

      <div class="mb-8 flex flex-wrap gap-4">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="city-filter">City</label>
          <select
            id="city-filter"
            v-model="selectedCity"
            class="rounded-xl border border-brand-line bg-white px-4 py-2.5 text-sm text-brand-ink outline-none focus:border-brand-rose"
          >
            <option v-for="city in CITIES" :key="city" :value="city">{{ city }}</option>
          </select>
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="spec-filter">Specialization</label>
          <select
            id="spec-filter"
            v-model="selectedSpecialization"
            class="rounded-xl border border-brand-line bg-white px-4 py-2.5 text-sm text-brand-ink outline-none focus:border-brand-rose"
          >
            <option v-for="spec in SPECIALIZATIONS" :key="spec || 'all'" :value="spec">
              {{ spec ? spec.charAt(0).toUpperCase() + spec.slice(1) : "All Specializations" }}
            </option>
          </select>
        </div>
      </div>

      <div v-if="loading" class="flex justify-center py-20">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-brand-line border-t-brand-rose"></div>
      </div>

      <div v-else-if="fetchError" class="glass-card px-6 py-12 text-center">
        <p class="font-semibold text-brand-plum">{{ fetchError }}</p>
        <button type="button" class="btn-primary mt-6" @click="fetchBeauticians">Try Again</button>
      </div>

      <div v-else-if="beauticians.length === 0" class="glass-card px-6 py-12 text-center">
        <p class="font-semibold text-brand-plum">No salons found</p>
        <p class="mt-2 text-sm text-brand-muted">{{ emptyHint }}</p>
        <button type="button" class="btn-ghost mt-4" @click="selectedSpecialization = ''; selectedGenderFocus = ''">
          Reset filters
        </button>
      </div>

      <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <article v-for="b in beauticians" :key="b.id" class="glass-card overflow-hidden">
          <div class="flex items-center gap-4 p-4 pb-0">
            <img
              v-if="b.profile_photo_url"
              :src="b.profile_photo_url"
              :alt="b.name"
              class="h-16 w-16 rounded-full object-cover ring-2 ring-[#f0dce8]"
            />
            <div
              v-else
              class="flex h-16 w-16 items-center justify-center rounded-full bg-[#fff0f5] text-lg font-bold text-brand-plum ring-2 ring-[#f0dce8]"
            >
              {{ initials(b.name) }}
            </div>
            <div>
              <h3 class="font-semibold text-brand-ink">{{ b.name }}</h3>
              <p class="text-sm font-medium text-brand-plum">{{ b.salon_name || "Salon" }}</p>
              <p class="text-sm text-brand-muted">
                {{ b.city }}{{ b.area ? ` · ${b.area}` : "" }}
              </p>
            </div>
          </div>

          <div class="p-4">
            <div class="mb-3 flex flex-wrap gap-1.5">
              <span
                class="rounded-md bg-[#fff0f5] px-2 py-0.5 text-xs font-semibold text-brand-plum"
              >
                {{ genderLabel(b.gender_focus) }}
              </span>
              <span
                v-for="spec in b.specializations"
                :key="spec"
                class="rounded-md bg-[#f0dce8]/60 px-2 py-0.5 text-xs capitalize text-brand-muted"
              >
                {{ spec }}
              </span>
            </div>

            <div class="mb-3 flex items-center justify-between">
              <span
                class="rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                :class="badgeClass(b.skill_badge)"
              >
                {{ b.skill_badge }}
              </span>
              <span class="text-sm text-amber-500">{{ renderStars(b.avg_rating) }}</span>
            </div>

            <p class="mb-4 text-sm font-medium text-brand-plum">
              Rs. {{ Number(b.hourly_rate).toLocaleString() }} / hr
            </p>

            <button type="button" class="btn-primary w-full" @click="handleBookNow(b)">Book Now</button>
          </div>
        </article>
      </div>
    </section>

    <BookingModal
      v-if="showModal && selectedBeautician"
      :beautician="selectedBeautician"
      @close="closeModal"
      @success="onBookingSuccess"
    />
  </LandingLayout>
</template>
