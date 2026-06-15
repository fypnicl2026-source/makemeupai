<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import LandingLayout from "../layouts/LandingLayout.vue";
import { authStore } from "../stores/auth";

const router = useRouter();

const email = ref("");
const password = ref("");
const errorMessage = ref("");
const fieldErrors = ref({});

async function handleSubmit() {
  errorMessage.value = "";
  fieldErrors.value = {};

  try {
    await authStore.login(email.value, password.value);
    if (authStore.isLoggedIn) {
      router.push("/dashboard");
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || "Unable to sign in. Please try again.";
    fieldErrors.value = error.response?.data?.errors || {};
  }
}
</script>

<template>
  <LandingLayout>
    <section class="container-shell py-16">
      <div class="glass-card mx-auto max-w-md p-8">
        <h1 class="text-2xl font-bold text-brand-plum">Sign In</h1>
        <p class="mt-2 text-sm text-[#6f6176]">Welcome back to MakemeupAI</p>

        <div
          v-if="errorMessage"
          class="mt-6 rounded-xl border border-[#f5c2d4] bg-[#fff0f5] px-4 py-3 text-sm text-brand-plum"
        >
          {{ errorMessage }}
        </div>

        <form class="mt-6 space-y-4" @submit.prevent="handleSubmit">
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
              autocomplete="current-password"
              class="w-full rounded-xl border border-[#f0dce8] bg-white px-4 py-2.5 text-[#1f1124] outline-none focus:border-brand-rose"
            />
            <p v-if="fieldErrors.password" class="mt-1 text-xs text-brand-plum">{{ fieldErrors.password[0] }}</p>
          </div>

          <button type="submit" class="btn-primary w-full" :disabled="authStore.loading">
            {{ authStore.loading ? "Signing in..." : "Sign In" }}
          </button>
        </form>

        <p class="mt-6 text-center text-sm text-[#6f6176]">
          Don't have an account?
          <RouterLink class="font-semibold text-brand-plum hover:underline" to="/signup">Sign up</RouterLink>
        </p>
      </div>
    </section>
  </LandingLayout>
</template>
