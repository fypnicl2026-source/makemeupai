<script setup>
import { onMounted, ref } from "vue";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import { cancelBooking, getMyBookings } from "../services/bookings";

const bookings = ref([]);
const loading = ref(false);
const error = ref("");
const cancellingId = ref(null);

function statusClass(status) {
  const classes = {
    pending: "bg-amber-100 text-amber-800",
    confirmed: "bg-green-100 text-green-800",
    cancelled: "bg-gray-100 text-gray-600",
  };
  return classes[status] || classes.pending;
}

function initials(name) {
  return name
    .split(" ")
    .map((part) => part[0])
    .join("")
    .slice(0, 2)
    .toUpperCase();
}

async function fetchBookings() {
  loading.value = true;
  error.value = "";
  try {
    bookings.value = await getMyBookings();
  } catch {
    error.value = "Could not load bookings. Please try again.";
  } finally {
    loading.value = false;
  }
}

async function handleCancel(id) {
  if (!confirm("Are you sure you want to cancel this booking?")) return;

  cancellingId.value = id;
  try {
    await cancelBooking(id);
    await fetchBookings();
  } catch {
    error.value = "Could not cancel booking. Please try again.";
  } finally {
    cancellingId.value = null;
  }
}

onMounted(() => {
  fetchBookings();
});
</script>

<template>
  <DashboardLayout>
    <section class="container-shell py-10">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-brand-plum">My Bookings</h1>
        <p class="mt-1 text-sm text-brand-muted">View and manage your beautician appointments.</p>
      </div>

      <div
        v-if="error"
        class="mb-6 rounded-xl border border-[#f5c2d4] bg-[#fff0f5] px-4 py-3 text-sm text-brand-plum"
      >
        {{ error }}
      </div>

      <div v-if="loading" class="flex justify-center py-20">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-brand-line border-t-brand-rose"></div>
      </div>

      <div v-else-if="bookings.length === 0" class="glass-card px-6 py-12 text-center">
        <p class="font-semibold text-brand-plum">No bookings yet</p>
        <p class="mt-2 text-sm text-brand-muted">Book a beautician to see your appointments here.</p>
        <RouterLink to="/beauticians" class="btn-primary mt-6 inline-block">Browse Beauticians</RouterLink>
      </div>

      <div v-else class="space-y-4">
        <article v-for="booking in bookings" :key="booking.id" class="glass-card p-5">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-4">
              <img
                v-if="booking.beautician.photo"
                :src="booking.beautician.photo"
                :alt="booking.beautician.name"
                class="h-14 w-14 rounded-full object-cover ring-2 ring-[#f0dce8]"
              />
              <div
                v-else
                class="flex h-14 w-14 items-center justify-center rounded-full bg-[#fff0f5] font-bold text-brand-plum ring-2 ring-[#f0dce8]"
              >
                {{ initials(booking.beautician.name) }}
              </div>
              <div>
                <h3 class="font-semibold text-brand-ink">{{ booking.beautician.name }}</h3>
                <p class="text-sm text-brand-muted">{{ booking.service_type }}</p>
                <p class="mt-1 text-sm text-brand-muted">
                  {{ booking.booking_date }} at {{ booking.booking_time }}
                </p>
              </div>
            </div>

            <div class="flex flex-col items-end gap-2">
              <span
                class="rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                :class="statusClass(booking.status)"
              >
                {{ booking.status }}
              </span>
              <p class="text-sm font-medium text-brand-plum">
                Rs. {{ Number(booking.price).toLocaleString() }}
              </p>
              <button
                v-if="booking.status === 'pending'"
                type="button"
                class="btn-ghost text-sm"
                :disabled="cancellingId === booking.id"
                @click="handleCancel(booking.id)"
              >
                {{ cancellingId === booking.id ? "Cancelling..." : "Cancel" }}
              </button>
            </div>
          </div>
        </article>
      </div>
    </section>
  </DashboardLayout>
</template>
