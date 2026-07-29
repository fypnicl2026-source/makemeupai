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

const step = computed(() => {
  if (lookResults.value) return 3;
  if (faceTraits.value?.faceShape) return 2;
  return 1;
});

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
    <section class="container-shell max-w-4xl py-6 md:py-8">
      <div class="mb-8">
        <h1 class="font-display text-3xl font-semibold text-brand-plum">Face Insights</h1>
        <p class="mt-2 max-w-xl text-sm leading-relaxed text-brand-muted">
          Selfie → gender &amp; event → personalized looks. Men get styling and grooming; women get
          makeup, hair, and mehndi.
        </p>
        <ol class="mt-5 flex flex-wrap gap-2 text-xs font-medium">
          <li
            v-for="(label, i) in ['1. Selfie', '2. Gender & event', '3. Looks']"
            :key="label"
            class="rounded-lg px-3 py-1.5 transition-colors"
            :class="
              step >= i + 1
                ? 'bg-brand-blush-deep text-brand-plum ring-1 ring-brand-rose/25'
                : 'bg-white/70 text-brand-muted ring-1 ring-brand-line/80'
            "
          >
            {{ label }}
          </li>
        </ol>
      </div>

      <div v-if="loadingProfile" class="flex justify-center py-16">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-brand-line border-t-brand-rose" />
      </div>

      <template v-else>
        <section class="glass-card mb-5 p-5 md:p-6">
          <h2 class="font-display text-lg font-semibold text-brand-plum">Your selfie</h2>
          <p class="mt-1 text-sm text-brand-muted">
            Clear, front-facing photos work best. Uploading again replaces your saved profile photo.
          </p>

          <div class="mt-5 flex flex-wrap items-start gap-6">
            <div
              class="flex h-32 w-32 items-center justify-center overflow-hidden rounded-2xl border border-brand-line/80 bg-brand-blush-deep/50 text-sm text-brand-muted"
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

        <section v-if="faceTraits?.faceShape" class="glass-card mb-5 p-5 md:p-6">
          <h2 class="font-display text-lg font-semibold text-brand-plum">Style traits</h2>
          <p class="mt-1 text-xs text-brand-muted">
            Heuristic style analysis (MVP) — suggestions tailored to these traits.
          </p>
          <dl class="mt-4 grid gap-3 sm:grid-cols-3">
            <div
              v-for="row in [
                { label: 'Face shape', value: faceTraits.faceShape },
                { label: 'Skin tone', value: faceTraits.skinTone },
                { label: 'Hair length', value: faceTraits.hairLength },
              ]"
              :key="row.label"
              class="rounded-xl border border-brand-line/70 bg-white/50 px-4 py-3"
            >
              <dt class="text-xs font-medium text-brand-muted">{{ row.label }}</dt>
              <dd class="mt-1 font-semibold capitalize text-brand-ink">{{ formatLabel(row.value) }}</dd>
            </div>
          </dl>
        </section>

        <section class="glass-card mb-5 p-5 md:p-6">
          <h2 class="font-display text-lg font-semibold text-brand-plum">Generate your complete look</h2>
          <p class="mt-1 text-sm text-brand-muted">
            Choose gender, event, and mood. Male looks focus on hair, beard, and styling only.
          </p>

          <div class="mt-5">
            <p class="mb-2 text-sm font-medium text-brand-muted">Gender</p>
            <div class="segment-group">
              <button
                v-for="g in GENDERS"
                :key="g.value"
                type="button"
                class="segment-btn"
                :class="selectedGender === g.value ? 'segment-btn-active' : ''"
                @click="selectedGender = g.value; lookResults = null"
              >
                {{ g.label }}
              </button>
            </div>
          </div>

          <div class="mt-5">
            <p class="mb-2 text-sm font-medium text-brand-muted">Event</p>
            <div class="segment-group">
              <button
                v-for="event in EVENT_TYPES"
                :key="event.value"
                type="button"
                class="segment-btn"
                :class="selectedEvent === event.value ? 'segment-btn-active' : ''"
                @click="selectedEvent = event.value"
              >
                {{ event.label }}
              </button>
            </div>
          </div>

          <div class="mt-5">
            <p class="mb-2 text-sm font-medium text-brand-muted">Style mood</p>
            <div class="segment-group">
              <button
                v-for="mood in STYLE_MOODS"
                :key="mood.value"
                type="button"
                class="segment-btn"
                :class="selectedMood === mood.value ? 'segment-btn-active' : ''"
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

        <section v-if="lookResults" class="glass-card mb-5 animate-fade-in p-5 md:p-6">
          <h2 class="font-display text-lg font-semibold text-brand-plum">Your look suggestions</h2>
          <p class="mt-1 text-sm text-brand-muted">
            Tailored for
            {{ lookResults.gender === "male" ? "men’s styling & grooming" : "makeup, hair & mehndi" }}.
          </p>
          <div class="mt-5 grid gap-4 md:grid-cols-3">
            <div
              v-for="col in resultColumns"
              :key="col.key"
              class="rounded-xl border border-brand-line/70 bg-white/50 p-4"
            >
              <h3 class="font-display text-base font-semibold text-brand-plum">{{ col.title }}</h3>
              <ul class="mt-3 space-y-1.5 text-sm leading-relaxed text-brand-muted">
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
