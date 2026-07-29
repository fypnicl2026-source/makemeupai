<script setup>
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import { getFaceProfile, getLookRecommendations, uploadFaceAnalysis } from "../services/ai";
import { authStore } from "../stores/auth";

const router = useRouter();

const GENDERS = [
  { label: "Female", value: "female" },
  { label: "Male", value: "male" },
];

const EVENT_TYPES = [
  { label: "Wedding", value: "wedding" },
  { label: "Party", value: "party" },
  { label: "Casual", value: "casual" },
  { label: "Work", value: "work" },
  { label: "Formal", value: "formal" },
];

const STYLE_MOODS = [
  { label: "Elegant", value: "elegant" },
  { label: "Natural", value: "natural" },
  { label: "Bold", value: "bold" },
  { label: "Soft", value: "soft" },
];

const profilePhotoUrl = ref(null);
const faceTraits = ref(null);
const selectedGender = ref(authStore.user?.gender === "male" || authStore.user?.gender === "female" ? authStore.user.gender : "");
const selectedEvent = ref("party");
const selectedMood = ref("elegant");
const lookResults = ref(null);

const loadingProfile = ref(true);
const uploading = ref(false);
const generating = ref(false);
const uploadError = ref("");
const generateError = ref("");

const canGenerate = computed(
  () =>
    Boolean(faceTraits.value?.faceShape) &&
    Boolean(selectedGender.value) &&
    Boolean(selectedEvent.value) &&
    Boolean(selectedMood.value)
);

const resultColumns = computed(() => {
  if (!lookResults.value) return [];
  if (lookResults.value.gender === "male" || selectedGender.value === "male") {
    return [
      { key: "hairstyle", title: "Hairstyle" },
      { key: "beard_grooming", title: "Beard & grooming" },
      { key: "styling", title: "Styling" },
    ];
  }
  return [
    { key: "makeup", title: "Makeup" },
    { key: "hairstyle", title: "Hairstyle" },
    { key: "mehndi", title: "Mehndi" },
  ];
});

function formatLabel(value) {
  if (!value) return "";
  return value
    .split("-")
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(" ");
}

async function loadProfile() {
  loadingProfile.value = true;
  try {
    const profile = await getFaceProfile();
    profilePhotoUrl.value = profile.profile_photo_url;
    faceTraits.value = profile.face_traits;
  } catch {
    profilePhotoUrl.value = authStore.user?.profile_photo_url ?? null;
    faceTraits.value = authStore.user?.face_traits ?? null;
  } finally {
    if (!selectedGender.value && (authStore.user?.gender === "male" || authStore.user?.gender === "female")) {
      selectedGender.value = authStore.user.gender;
    }
    loadingProfile.value = false;
  }
}

async function onFileChange(event) {
  const file = event.target.files?.[0];
  if (!file) return;

  uploadError.value = "";
  uploading.value = true;
  lookResults.value = null;

  const formData = new FormData();
  formData.append("image", file);

  try {
    const result = await uploadFaceAnalysis(formData);
    profilePhotoUrl.value = result.profile_photo_url;
    faceTraits.value = {
      faceShape: result.faceShape,
      skinTone: result.skinTone,
      hairLength: result.hairLength,
      analyzed_at: result.analyzed_at,
    };
    await authStore.fetchUser();
  } catch (err) {
    uploadError.value =
      err.response?.data?.message || "Could not analyze your photo. Please try again.";
  } finally {
    uploading.value = false;
    event.target.value = "";
  }
}

async function generateLook() {
  if (!faceTraits.value?.faceShape) {
    generateError.value = "Upload a selfie first to unlock look recommendations.";
    return;
  }
  if (!selectedGender.value) {
    generateError.value = "Select your gender so we can tailor styling options.";
    return;
  }

  generateError.value = "";
  generating.value = true;
  lookResults.value = null;

  try {
    lookResults.value = await getLookRecommendations({
      eventType: selectedEvent.value,
      styleMood: selectedMood.value,
      gender: selectedGender.value,
    });
    await authStore.fetchUser();
  } catch (err) {
    generateError.value =
      err.response?.data?.message || "Could not generate recommendations. Please try again.";
  } finally {
    generating.value = false;
  }
}

function occasionForOutfits() {
  const map = {
    wedding: "formal",
    formal: "formal",
    party: "party",
    work: "work",
    casual: "casual",
  };
  return map[selectedEvent.value] || "casual";
}

function goToOutfitIdeas() {
  router.push({ path: "/recommendations", query: { occasion: occasionForOutfits() } });
}

onMounted(() => {
  loadProfile();
});
</script>

<template>
  <DashboardLayout>
    <section class="container-shell max-w-4xl py-10">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-brand-plum">Face Insights</h1>
        <p class="mt-1 text-sm text-brand-muted">
          Selfie → gender &amp; event → personalized looks. Men get styling and grooming; women get
          makeup, hair, and mehndi.
        </p>
        <ol class="mt-4 flex flex-wrap gap-2 text-xs font-medium text-brand-muted">
          <li class="rounded-full bg-[#fff0f5] px-3 py-1 text-brand-plum ring-1 ring-brand-rose/40">1. Selfie</li>
          <li class="rounded-full bg-white px-3 py-1 ring-1 ring-brand-line">2. Gender &amp; event</li>
          <li class="rounded-full bg-white px-3 py-1 ring-1 ring-brand-line">3. Looks</li>
        </ol>
      </div>

      <div v-if="loadingProfile" class="flex justify-center py-16">
        <div
          class="h-10 w-10 animate-spin rounded-full border-4 border-brand-line border-t-brand-rose"
        ></div>
      </div>

      <template v-else>
        <section class="glass-card mb-6 p-5">
          <h2 class="text-lg font-semibold text-brand-plum">Your selfie</h2>
          <p class="mt-1 text-sm text-brand-muted">
            Clear, front-facing photos work best. Uploading again replaces your saved profile photo.
          </p>

          <div class="mt-4 flex flex-wrap items-start gap-6">
            <div
              class="flex h-32 w-32 items-center justify-center overflow-hidden rounded-2xl border border-brand-line bg-[#fff0f5] text-sm text-brand-muted"
            >
              <img
                v-if="profilePhotoUrl"
                :src="profilePhotoUrl"
                alt="Your profile selfie"
                class="h-full w-full object-cover"
              />
              <span v-else>No photo yet</span>
            </div>

            <div>
              <label
                class="btn-primary inline-block cursor-pointer"
                :class="{ 'pointer-events-none opacity-60': uploading }"
              >
                {{ uploading ? "Analyzing…" : profilePhotoUrl ? "Replace selfie" : "Upload selfie" }}
                <input
                  type="file"
                  accept="image/*"
                  class="hidden"
                  :disabled="uploading"
                  @change="onFileChange"
                />
              </label>
              <p v-if="uploadError" class="mt-2 text-sm text-brand-plum">{{ uploadError }}</p>
            </div>
          </div>
        </section>

        <section v-if="faceTraits?.faceShape" class="glass-card mb-6 p-5">
          <h2 class="text-lg font-semibold text-brand-plum">Style traits</h2>
          <p class="mt-1 text-xs text-brand-muted">
            Heuristic style analysis (MVP) — suggestions tailored to these traits.
          </p>
          <dl class="mt-4 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-brand-line bg-white/60 px-4 py-3">
              <dt class="text-xs font-medium text-brand-muted">Face shape</dt>
              <dd class="mt-1 font-semibold capitalize text-brand-ink">
                {{ formatLabel(faceTraits.faceShape) }}
              </dd>
            </div>
            <div class="rounded-xl border border-brand-line bg-white/60 px-4 py-3">
              <dt class="text-xs font-medium text-brand-muted">Skin tone</dt>
              <dd class="mt-1 font-semibold capitalize text-brand-ink">
                {{ formatLabel(faceTraits.skinTone) }}
              </dd>
            </div>
            <div class="rounded-xl border border-brand-line bg-white/60 px-4 py-3">
              <dt class="text-xs font-medium text-brand-muted">Hair length</dt>
              <dd class="mt-1 font-semibold capitalize text-brand-ink">
                {{ formatLabel(faceTraits.hairLength) }}
              </dd>
            </div>
          </dl>
        </section>

        <section class="glass-card mb-6 p-5">
          <h2 class="text-lg font-semibold text-brand-plum">Generate your complete look</h2>
          <p class="mt-1 text-sm text-brand-muted">
            Choose gender, event, and mood. Male looks focus on hair, beard, and styling only.
          </p>

          <div class="mt-4">
            <p class="mb-2 text-sm font-medium text-brand-muted">Gender</p>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="g in GENDERS"
                :key="g.value"
                type="button"
                class="rounded-full px-4 py-2 text-sm transition-colors"
                :class="
                  selectedGender === g.value
                    ? 'bg-[#fff0f5] font-semibold text-brand-plum ring-1 ring-brand-rose'
                    : 'bg-white text-brand-muted hover:text-brand-plum'
                "
                @click="selectedGender = g.value; lookResults = null"
              >
                {{ g.label }}
              </button>
            </div>
          </div>

          <div class="mt-4">
            <p class="mb-2 text-sm font-medium text-brand-muted">Event</p>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="event in EVENT_TYPES"
                :key="event.value"
                type="button"
                class="rounded-full px-4 py-2 text-sm transition-colors"
                :class="
                  selectedEvent === event.value
                    ? 'bg-[#fff0f5] font-semibold text-brand-plum ring-1 ring-brand-rose'
                    : 'bg-white text-brand-muted hover:text-brand-plum'
                "
                @click="selectedEvent = event.value"
              >
                {{ event.label }}
              </button>
            </div>
          </div>

          <div class="mt-4">
            <p class="mb-2 text-sm font-medium text-brand-muted">Style mood</p>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="mood in STYLE_MOODS"
                :key="mood.value"
                type="button"
                class="rounded-full px-4 py-2 text-sm transition-colors"
                :class="
                  selectedMood === mood.value
                    ? 'bg-[#fff0f5] font-semibold text-brand-plum ring-1 ring-brand-rose'
                    : 'bg-white text-brand-muted hover:text-brand-plum'
                "
                @click="selectedMood = mood.value"
              >
                {{ mood.label }}
              </button>
            </div>
          </div>

          <button
            type="button"
            class="btn-primary mt-6"
            :disabled="generating || !canGenerate"
            @click="generateLook"
          >
            {{ generating ? "Generating…" : "Generate look recommendations" }}
          </button>
          <p v-if="!canGenerate && faceTraits?.faceShape" class="mt-2 text-xs text-brand-muted">
            Select gender, event, and mood to continue.
          </p>
          <p v-if="generateError" class="mt-2 text-sm text-brand-plum">{{ generateError }}</p>
        </section>

        <section v-if="lookResults" class="glass-card mb-6 p-5">
          <h2 class="text-lg font-semibold text-brand-plum">Your look suggestions</h2>
          <p class="mt-1 text-sm text-brand-muted">
            Tailored for
            {{ lookResults.gender === "male" ? "men’s styling & grooming" : "makeup, hair & mehndi" }}.
          </p>
          <div class="mt-4 grid gap-4 md:grid-cols-3">
            <div
              v-for="col in resultColumns"
              :key="col.key"
              class="rounded-xl border border-brand-line bg-white/60 p-4"
            >
              <h3 class="font-semibold text-brand-plum">{{ col.title }}</h3>
              <ul class="mt-2 space-y-1 text-sm text-brand-muted">
                <li v-for="item in lookResults[col.key] || []" :key="item">{{ item }}</li>
              </ul>
            </div>
          </div>
          <button type="button" class="btn-ghost mt-6" @click="goToOutfitIdeas">
            Get outfit ideas from your wardrobe →
          </button>
        </section>
      </template>
    </section>
  </DashboardLayout>
</template>
