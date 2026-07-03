<script setup>
import { onMounted, ref, watch } from "vue";
import { showToast } from "../composables/useToast";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import { addItem, deleteItem, getItems } from "../services/wardrobe";

const CATEGORIES = [
  { label: "All", value: null },
  { label: "Tops", value: "tops" },
  { label: "Bottoms", value: "bottoms" },
  { label: "Dresses", value: "dresses" },
  { label: "Shoes", value: "shoes" },
  { label: "Accessories", value: "accessories" },
  { label: "Outerwear", value: "outerwear" },
];

const SEASONS = ["spring", "summer", "fall", "winter"];
const OCCASIONS = ["casual", "work", "formal", "party"];

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

const items = ref([]);
const loading = ref(false);
const fetchError = ref("");
const activeCategory = ref(null);
const showModal = ref(false);
const submitting = ref(false);
const formError = ref("");

const name = ref("");
const category = ref("");
const colorsInput = ref("");
const selectedSeasons = ref([]);
const selectedOccasions = ref([]);
const imageFile = ref(null);
const notes = ref("");

function colorToCss(colorName) {
  if (!colorName) return "#ca4d91";
  return COLOR_MAP[colorName.toLowerCase().trim()] || "#ca4d91";
}

function resetForm() {
  name.value = "";
  category.value = "";
  colorsInput.value = "";
  selectedSeasons.value = [];
  selectedOccasions.value = [];
  imageFile.value = null;
  notes.value = "";
  formError.value = "";
}

function openModal() {
  resetForm();
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  resetForm();
}

function toggleSeason(season) {
  const index = selectedSeasons.value.indexOf(season);
  if (index === -1) {
    selectedSeasons.value.push(season);
  } else {
    selectedSeasons.value.splice(index, 1);
  }
}

function toggleOccasion(occasion) {
  const index = selectedOccasions.value.indexOf(occasion);
  if (index === -1) {
    selectedOccasions.value.push(occasion);
  } else {
    selectedOccasions.value.splice(index, 1);
  }
}

function onImageChange(event) {
  imageFile.value = event.target.files[0] || null;
}

async function fetchItems() {
  loading.value = true;
  fetchError.value = "";
  try {
    items.value = await getItems(activeCategory.value);
  } catch (error) {
    items.value = [];
    fetchError.value =
      error.response?.data?.message || "Failed to load wardrobe items. Please try again.";
  } finally {
    loading.value = false;
  }
}

async function handleDelete(id) {
  if (!confirm("Are you sure you want to delete this item?")) return;

  try {
    await deleteItem(id);
    await fetchItems();
    showToast("Item deleted.", "success");
  } catch (error) {
    showToast(error.response?.data?.message || "Failed to delete item.", "error");
  }
}

async function handleAddSubmit() {
  formError.value = "";

  const colors = colorsInput.value
    .split(",")
    .map((c) => c.trim())
    .filter(Boolean);

  if (!name.value.trim()) {
    formError.value = "Name is required.";
    return;
  }
  if (!category.value) {
    formError.value = "Category is required.";
    return;
  }
  if (colors.length === 0) {
    formError.value = "At least one color is required.";
    return;
  }
  if (selectedSeasons.value.length === 0) {
    formError.value = "Select at least one season.";
    return;
  }
  if (selectedOccasions.value.length === 0) {
    formError.value = "Select at least one occasion.";
    return;
  }

  const formData = new FormData();
  formData.append("name", name.value.trim());
  formData.append("category", category.value);
  colors.forEach((c) => formData.append("colors[]", c));
  selectedSeasons.value.forEach((s) => formData.append("season[]", s));
  selectedOccasions.value.forEach((o) => formData.append("occasion[]", o));
  if (notes.value.trim()) {
    formData.append("notes", notes.value.trim());
  }
  if (imageFile.value) {
    formData.append("image", imageFile.value);
  }

  submitting.value = true;
  try {
    await addItem(formData);
    closeModal();
    await fetchItems();
  } catch (error) {
    formError.value = error.response?.data?.message || "Failed to add item.";
  } finally {
    submitting.value = false;
  }
}

watch(activeCategory, () => {
  fetchItems();
});

onMounted(() => {
  fetchItems();
});
</script>

<template>
  <DashboardLayout>
    <section class="container-shell py-10 pb-24">
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-brand-plum">My Wardrobe</h1>
        <p class="mt-1 text-sm text-brand-muted">Manage your clothing items and get better outfit suggestions.</p>
      </div>

      <div class="mb-8 flex gap-1 overflow-x-auto border-b border-brand-line pb-1">
        <button
          v-for="cat in CATEGORIES"
          :key="cat.label"
          type="button"
          class="whitespace-nowrap px-4 py-2 text-sm transition-colors"
          :class="
            activeCategory === cat.value
              ? 'border-b-2 border-brand-rose font-semibold text-brand-plum'
              : 'text-brand-muted hover:text-brand-plum'
          "
          @click="activeCategory = cat.value"
        >
          {{ cat.label }}
        </button>
      </div>

      <div v-if="loading" class="flex justify-center py-20">
        <div
          class="h-10 w-10 animate-spin rounded-full border-4 border-brand-line border-t-brand-rose"
        ></div>
      </div>

      <div v-else-if="fetchError" class="glass-card px-6 py-12 text-center">
        <p class="font-semibold text-brand-plum">{{ fetchError }}</p>
        <button type="button" class="btn-primary mt-6" @click="fetchItems">Try Again</button>
      </div>

      <div
        v-else-if="items.length === 0"
        class="glass-card flex flex-col items-center justify-center px-6 py-16 text-center"
      >
        <p class="text-lg font-semibold text-brand-plum">Your wardrobe is empty</p>
        <p class="mt-2 text-sm text-brand-muted">
          Add your first item to start building your digital wardrobe.
        </p>
        <button type="button" class="btn-primary mt-6" @click="openModal">Add Item</button>
      </div>

      <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <article v-for="item in items" :key="item.id" class="glass-card overflow-hidden">
          <div class="relative aspect-square">
            <img
              v-if="item.image_url"
              :src="item.image_url"
              :alt="item.name"
              class="h-full w-full object-cover"
            />
            <div
              v-else
              class="flex h-full w-full items-center justify-center"
              :style="{ backgroundColor: colorToCss(item.colors?.[0]) }"
            >
              <span class="text-sm font-medium capitalize text-white/90 drop-shadow">
                {{ item.colors?.[0] || "No image" }}
              </span>
            </div>
            <button
              type="button"
              class="absolute right-2 top-2 rounded-lg bg-white/90 p-2 text-brand-muted shadow hover:bg-white hover:text-red-500"
              aria-label="Delete item"
              @click="handleDelete(item.id)"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14z" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M10 11v6M14 11v6" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
          </div>
          <div class="p-4">
            <h3 class="font-semibold text-brand-ink">{{ item.name }}</h3>
            <span
              class="mt-2 inline-block rounded-full bg-[#fff0f5] px-2.5 py-0.5 text-xs font-medium capitalize text-brand-plum"
            >
              {{ item.category }}
            </span>
            <div v-if="item.occasion?.length" class="mt-3 flex flex-wrap gap-1.5">
              <span
                v-for="tag in item.occasion"
                :key="tag"
                class="rounded-md bg-[#f0dce8]/60 px-2 py-0.5 text-xs capitalize text-brand-muted"
              >
                {{ tag }}
              </span>
            </div>
          </div>
        </article>
      </div>

      <button
        type="button"
        class="btn-primary fixed bottom-6 right-6 flex h-14 w-14 items-center justify-center rounded-full text-2xl shadow-lg"
        aria-label="Add item"
        @click="openModal"
      >
        +
      </button>
    </section>

    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
      @click.self="closeModal"
    >
      <div class="glass-card max-h-[90vh] w-full max-w-lg overflow-y-auto p-6">
        <h2 class="text-xl font-bold text-brand-plum">Add Clothing Item</h2>

        <div
          v-if="formError"
          class="mt-4 rounded-xl border border-[#f5c2d4] bg-[#fff0f5] px-4 py-3 text-sm text-brand-plum"
        >
          {{ formError }}
        </div>

        <form class="mt-4 space-y-4" @submit.prevent="handleAddSubmit">
          <div>
            <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="item-name">Name</label>
            <input
              id="item-name"
              v-model="name"
              type="text"
              required
              class="w-full rounded-xl border border-brand-line bg-white px-4 py-2.5 text-brand-ink outline-none focus:border-brand-rose"
            />
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="item-category">Category</label>
            <select
              id="item-category"
              v-model="category"
              required
              class="w-full rounded-xl border border-brand-line bg-white px-4 py-2.5 text-brand-ink outline-none focus:border-brand-rose"
            >
              <option value="" disabled>Select category</option>
              <option v-for="cat in CATEGORIES.filter((c) => c.value)" :key="cat.value" :value="cat.value">
                {{ cat.label }}
              </option>
            </select>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="item-colors">Colors</label>
            <input
              id="item-colors"
              v-model="colorsInput"
              type="text"
              placeholder="e.g. red, black"
              required
              class="w-full rounded-xl border border-brand-line bg-white px-4 py-2.5 text-brand-ink outline-none focus:border-brand-rose"
            />
          </div>

          <div>
            <p class="mb-2 text-sm font-medium text-brand-muted">Season</p>
            <div class="flex flex-wrap gap-3">
              <label v-for="season in SEASONS" :key="season" class="flex cursor-pointer items-center gap-2 text-sm capitalize">
                <input
                  type="checkbox"
                  :checked="selectedSeasons.includes(season)"
                  class="rounded border-brand-line text-brand-rose focus:ring-brand-rose"
                  @change="toggleSeason(season)"
                />
                {{ season }}
              </label>
            </div>
          </div>

          <div>
            <p class="mb-2 text-sm font-medium text-brand-muted">Occasion</p>
            <div class="flex flex-wrap gap-3">
              <label v-for="occasion in OCCASIONS" :key="occasion" class="flex cursor-pointer items-center gap-2 text-sm capitalize">
                <input
                  type="checkbox"
                  :checked="selectedOccasions.includes(occasion)"
                  class="rounded border-brand-line text-brand-rose focus:ring-brand-rose"
                  @change="toggleOccasion(occasion)"
                />
                {{ occasion }}
              </label>
            </div>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="item-image">Image</label>
            <input
              id="item-image"
              type="file"
              accept="image/*"
              class="w-full text-sm text-brand-muted file:mr-3 file:rounded-lg file:border-0 file:bg-[#fff0f5] file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-brand-plum"
              @change="onImageChange"
            />
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="item-notes">Notes</label>
            <textarea
              id="item-notes"
              v-model="notes"
              rows="3"
              class="w-full rounded-xl border border-brand-line bg-white px-4 py-2.5 text-brand-ink outline-none focus:border-brand-rose"
            ></textarea>
          </div>

          <div class="flex gap-3 pt-2">
            <button type="button" class="btn-ghost flex-1" @click="closeModal">Cancel</button>
            <button type="submit" class="btn-primary flex-1" :disabled="submitting">
              {{ submitting ? "Adding..." : "Add Item" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </DashboardLayout>
</template>
