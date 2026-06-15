<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import LandingLayout from "../layouts/LandingLayout.vue";
import { authStore } from "../stores/auth";

const router = useRouter();

const name = ref("");
const email = ref("");
const password = ref("");
const confirmPassword = ref("");
const city = ref("");
const errorMessage = ref("");
const fieldErrors = ref({});

async function handleSubmit() {
  errorMessage.value = "";
  fieldErrors.value = {};

  if (password.value.length < 8) {
    errorMessage.value = "Password must be at least 8 characters.";
    return;
  }

  if (password.value !== confirmPassword.value) {
    errorMessage.value = "Passwords do not match.";
    return;
  }

  try {
    await authStore.register(name.value, email.value, password.value, confirmPassword.value, city.value);
    if (authStore.isLoggedIn) {
      router.push("/dashboard");
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || "Unable to create account. Please try again.";
    fieldErrors.value = error.response?.data?.errors || {};
  }
}
</script>

<template>
  <LandingLayout>
    <section class="container-shell py-16">
      <div class="glass-card mx-auto max-w-md p-8">
        <h1 class="text-2xl font-bold text-brand-plum">Create Account</h1>
        <p class="mt-2 text-sm text-[#6f6176]">Join MakemeupAI and start building your look</p>

        <div
          v-if="errorMessage"
          class="mt-6 rounded-xl border border-[#f5c2d4] bg-[#fff0f5] px-4 py-3 text-sm text-brand-plum"
        >
          {{ errorMessage }}
        </div>

        <form class="mt-6 space-y-4" @submit.prevent="handleSubmit">
          <div>
            <label class="mb-1.5 block text-sm font-medium text-[#6f6176]" for="name">Name</label>
            <input
              id="name"
              v-model="name"
              type="text"
              required
              autocomplete="name"
              class="w-full rounded-xl border border-[#f0dce8] bg-white px-4 py-2.5 text-[#1f1124] outline-none focus:border-brand-rose"
            />
            <p v-if="fieldErrors.name" class="mt-1 text-xs text-brand-plum">{{ fieldErrors.name[0] }}</p>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-[#6f6176]" for="email">Email</label>
            <input
              id="email"
              v-model="email"
              type="email"
              required
              autocomplete="email"
              class="w-full rounded-xl border border-[#f0dce8] bg-white px-4 py-2.5 text-[#1f1124] outline-none focus:border-brand-rose"
            />
            <p v-if="fieldErrors.email" class="mt-1 text-xs text-brand-plum">{{ fieldErrors.email[0] }}</p>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-[#6f6176]" for="password">Password</label>
            <input
              id="password"
              v-model="password"
              type="password"
              required
              minlength="8"
              autocomplete="new-password"
              class="w-full rounded-xl border border-[#f0dce8] bg-white px-4 py-2.5 text-[#1f1124] outline-none focus:border-brand-rose"
            />
            <p v-if="fieldErrors.password" class="mt-1 text-xs text-brand-plum">{{ fieldErrors.password[0] }}</p>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-[#6f6176]" for="confirmPassword">
              Confirm Password
            </label>
            <input
              id="confirmPassword"
              v-model="confirmPassword"
              type="password"
              required
              minlength="8"
              autocomplete="new-password"
              class="w-full rounded-xl border border-[#f0dce8] bg-white px-4 py-2.5 text-[#1f1124] outline-none focus:border-brand-rose"
            />
            <p v-if="fieldErrors.password_confirmation" class="mt-1 text-xs text-brand-plum">
              {{ fieldErrors.password_confirmation[0] }}
            </p>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-[#6f6176]" for="city">City</label>
            <input
              id="city"
              v-model="city"
              type="text"
              required
              autocomplete="address-level2"
              class="w-full rounded-xl border border-[#f0dce8] bg-white px-4 py-2.5 text-[#1f1124] outline-none focus:border-brand-rose"
            />
            <p v-if="fieldErrors.city" class="mt-1 text-xs text-brand-plum">{{ fieldErrors.city[0] }}</p>
          </div>

          <button type="submit" class="btn-primary w-full" :disabled="authStore.loading">
            {{ authStore.loading ? "Creating account..." : "Sign Up" }}
          </button>
        </form>

        <p class="mt-6 text-center text-sm text-[#6f6176]">
          Already have an account?
          <RouterLink class="font-semibold text-brand-plum hover:underline" to="/signin">Sign in</RouterLink>
        </p>
      </div>
    </section>
  </LandingLayout>
</template>
