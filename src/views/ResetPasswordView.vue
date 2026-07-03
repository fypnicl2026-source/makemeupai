<script setup>
import { ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import BrandLogo from "../components/BrandLogo.vue";
import LandingLayout from "../layouts/LandingLayout.vue";
import { authStore } from "../stores/auth";

const route = useRoute();
const router = useRouter();

const password = ref("");
const confirmPassword = ref("");
const errorMessage = ref("");
const fieldErrors = ref({});

const token = ref(route.query.token || "");
const email = ref(route.query.email || "");

function isValidEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
}

async function handleSubmit() {
  errorMessage.value = "";
  fieldErrors.value = {};

  if (!token.value || !email.value) {
    errorMessage.value = "Invalid reset link. Please request a new password reset.";
    return;
  }

  if (!isValidEmail(email.value)) {
    errorMessage.value = "Please enter a valid email address.";
    return;
  }

  if (password.value.length < 8) {
    errorMessage.value = "Password must be at least 8 characters.";
    return;
  }

  if (password.value !== confirmPassword.value) {
    errorMessage.value = "Passwords do not match.";
    return;
  }

  try {
    await authStore.resetPassword(token.value, email.value, password.value, confirmPassword.value);
    router.push("/signin");
  } catch (error) {
    errorMessage.value = error.response?.data?.message || "Unable to reset password. Please try again.";
    fieldErrors.value = error.response?.data?.errors || {};
  }
}
</script>

<template>
  <LandingLayout>
    <section class="container-shell flex min-h-[calc(100vh-200px)] items-center py-16">
      <div class="auth-card">
        <div class="mb-8 flex justify-center">
          <BrandLogo size="lg" :show-text="false" to="/" />
        </div>
        <h1 class="text-center text-2xl font-bold text-brand-ink">Reset password</h1>
        <p class="mt-2 text-center text-sm text-brand-muted">Choose a new password for your account.</p>

        <div v-if="errorMessage" class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
          {{ errorMessage }}
        </div>

        <form class="mt-8 space-y-4" @submit.prevent="handleSubmit">
          <div>
            <label class="mb-1.5 block text-sm font-semibold text-brand-muted" for="email">Email</label>
            <input id="email" v-model="email" type="email" required autocomplete="email" class="input-field" />
            <p v-if="fieldErrors.email" class="mt-1 text-xs text-brand-rose">{{ fieldErrors.email[0] }}</p>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-semibold text-brand-muted" for="password">New password</label>
            <input id="password" v-model="password" type="password" required minlength="8" autocomplete="new-password" class="input-field" />
            <p v-if="fieldErrors.password" class="mt-1 text-xs text-brand-rose">{{ fieldErrors.password[0] }}</p>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-semibold text-brand-muted" for="confirmPassword">Confirm password</label>
            <input id="confirmPassword" v-model="confirmPassword" type="password" required minlength="8" autocomplete="new-password" class="input-field" />
          </div>

          <button type="submit" class="btn-primary w-full py-3" :disabled="authStore.loading">
            {{ authStore.loading ? "Resetting..." : "Reset password" }}
          </button>
        </form>
      </div>
    </section>
  </LandingLayout>
</template>
