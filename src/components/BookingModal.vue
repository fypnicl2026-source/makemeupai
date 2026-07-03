<script setup>
import { computed, ref } from "vue";
import { createBooking } from "../services/bookings";

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

const SERVICE_OPTIONS = [
  "Makeup Session",
  "Hairstyling",
  "Mehndi",
  "Bridal Package",
];

const today = computed(() => new Date().toISOString().split("T")[0]);

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
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="handleClose">
    <div class="glass-card w-full max-w-md p-6">
      <h2 class="text-xl font-bold text-brand-plum">Book {{ beautician.name }}</h2>

      <div
        v-if="errorMessage"
        class="mt-4 rounded-xl border border-[#f5c2d4] bg-[#fff0f5] px-4 py-3 text-sm text-brand-plum"
      >
        {{ errorMessage }}
      </div>

      <form class="mt-4 space-y-4" @submit.prevent="handleSubmit">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="service-type">Service Type</label>
          <select
            id="service-type"
            v-model="serviceType"
            required
            class="w-full rounded-xl border border-brand-border bg-white px-4 py-2.5 text-[#1f1124] outline-none focus:border-brand-rose"
          >
            <option value="" disabled>Select service</option>
            <option v-for="option in SERVICE_OPTIONS" :key="option" :value="option">
              {{ option }}
            </option>
          </select>
        </div>

        <div>
          <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="booking-date">Date</label>
          <input
            id="booking-date"
            v-model="bookingDate"
            type="date"
            required
            :min="today"
            class="w-full rounded-xl border border-brand-border bg-white px-4 py-2.5 text-[#1f1124] outline-none focus:border-brand-rose"
          />
        </div>

        <div>
          <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="booking-time">Time</label>
          <input
            id="booking-time"
            v-model="bookingTime"
            type="time"
            required
            class="w-full rounded-xl border border-brand-border bg-white px-4 py-2.5 text-[#1f1124] outline-none focus:border-brand-rose"
          />
        </div>

        <div>
          <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="booking-notes">Notes</label>
          <textarea
            id="booking-notes"
            v-model="notes"
            rows="3"
            placeholder="Any special requests..."
            class="w-full rounded-xl border border-brand-border bg-white px-4 py-2.5 text-[#1f1124] outline-none focus:border-brand-rose"
          ></textarea>
        </div>

        <p class="text-sm font-medium text-brand-plum">
          Estimated: Rs. {{ Number(beautician.hourly_rate).toLocaleString() }}
        </p>

        <div class="flex gap-3 pt-2">
          <button type="button" class="btn-ghost flex-1" @click="handleClose">Cancel</button>
          <button type="submit" class="btn-primary flex-1" :disabled="submitting">
            {{ submitting ? "Booking..." : "Confirm Booking" }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
