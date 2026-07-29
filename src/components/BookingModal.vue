<script setup>
import { computed, ref, watch } from "vue";
import { createBooking } from "../services/bookings";

const FEMALE_SERVICES = [
  "Makeup Session",
  "Hairstyling",
  "Mehndi",
  "Bridal Package",
];

const MALE_SERVICES = [
  "Haircut & Styling",
  "Beard Grooming",
  "Groom Package",
  "Party Styling",
];

const props = defineProps({
  beautician: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["close", "success"]);

const serviceType = ref("");
const bookingDate = ref("");
const bookingTime = ref("");
const notes = ref("");
const submitting = ref(false);
const errorMessage = ref("");

const serviceOptions = computed(() => {
  if (Array.isArray(props.beautician.allowed_services) && props.beautician.allowed_services.length) {
    return props.beautician.allowed_services;
  }
  if (props.beautician.gender_focus === "male") return MALE_SERVICES;
  if (props.beautician.gender_focus === "female") return FEMALE_SERVICES;
  return [...FEMALE_SERVICES, ...MALE_SERVICES];
});

const today = computed(() => {
  const d = new Date();
  d.setDate(d.getDate() + 1);
  return d.toISOString().split("T")[0];
});

watch(
  serviceOptions,
  (opts) => {
    if (serviceType.value && !opts.includes(serviceType.value)) {
      serviceType.value = "";
    }
  },
  { immediate: true }
);

function resetForm() {
  serviceType.value = "";
  bookingDate.value = "";
  bookingTime.value = "";
  notes.value = "";
  errorMessage.value = "";
}

function handleClose() {
  resetForm();
  emit("close");
}

async function handleSubmit() {
  errorMessage.value = "";

  if (!serviceType.value || !bookingDate.value || !bookingTime.value) {
    errorMessage.value = "Please fill in all required fields.";
    return;
  }

  submitting.value = true;
  try {
    await createBooking({
      beautician_id: props.beautician.id,
      service_type: serviceType.value,
      booking_date: bookingDate.value,
      booking_time: bookingTime.value,
      notes: notes.value.trim() || undefined,
    });
    resetForm();
    emit("success");
    emit("close");
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || "Could not create booking. Please try again.";
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-brand-ink/35 p-4 backdrop-blur-[2px]"
    @click.self="handleClose"
  >
    <div class="glass-card w-full max-w-md animate-fade-in p-6 shadow-elevated">
      <h2 class="font-display text-xl font-semibold text-brand-plum">
        Book {{ beautician.salon_name || beautician.name }}
      </h2>
      <p class="mt-1 text-sm text-brand-muted">
        {{ beautician.name }}
        <span v-if="beautician.area"> · {{ beautician.area }}, {{ beautician.city }}</span>
        <span v-else> · {{ beautician.city }}</span>
      </p>

      <div
        v-if="errorMessage"
        class="mt-4 rounded-xl border border-brand-rose/25 bg-brand-blush-deep px-4 py-3 text-sm text-brand-plum"
      >
        {{ errorMessage }}
      </div>

      <form class="mt-5 space-y-4" @submit.prevent="handleSubmit">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="service-type">Service</label>
          <select id="service-type" v-model="serviceType" required class="input-field">
            <option value="" disabled>Select service</option>
            <option v-for="option in serviceOptions" :key="option" :value="option">
              {{ option }}
            </option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="booking-date">Date</label>
            <input
              id="booking-date"
              v-model="bookingDate"
              type="date"
              required
              :min="today"
              class="input-field"
            />
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="booking-time">Time</label>
            <input id="booking-time" v-model="bookingTime" type="time" required class="input-field" />
          </div>
        </div>

        <div>
          <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="booking-notes">Notes</label>
          <textarea
            id="booking-notes"
            v-model="notes"
            rows="2"
            placeholder="Any special requests…"
            class="input-field resize-none"
          />
        </div>

        <p class="text-sm font-medium text-brand-plum">
          Estimated
          <span class="text-brand-ink">Rs. {{ Number(beautician.hourly_rate).toLocaleString() }}</span>
        </p>

        <div class="flex gap-3 pt-1">
          <button type="button" class="btn-ghost flex-1" @click="handleClose">Cancel</button>
          <button type="submit" class="btn-primary flex-1" :disabled="submitting">
            {{ submitting ? "Booking…" : "Confirm" }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
