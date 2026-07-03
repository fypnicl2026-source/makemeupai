<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import BrandLogo from "../components/BrandLogo.vue";
import LandingLayout from "../layouts/LandingLayout.vue";
import { authStore } from "../stores/auth";

const router = useRouter();
const email = ref("");
const errorMessage = ref("");
const fieldErrors = ref({});
const submitted = ref(false);

function isValidEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
}

async function handleSubmit() {
  errorMessage.value = "";
  fieldErrors.value = {};

  if (!isValidEmail(email.value)) {
    errorMessage.value = "Please enter a valid email address.";
    return;
  }

  try {
    await authStore.forgotPassword(email.value);
    submitted.value = true;
  } catch (error) {
    errorMessage.value = error.response?.data?.message || "Unable to send reset link. Please try again.";
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

        <template v-if="submitted">
          <div class="text-center">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-brand-blush-deep text-3xl">
              ✉
            </div>
            <h1 class="text-2xl font-bold text-brand-ink">Check your email</h1>
            <p class="mt-3 text-sm text-brand-muted">
              If an account exists for <span class="font-bold text-brand-plum">{{ email }}</span>, we sent a password reset link.
            </p>
            <button type="button" class="btn-primary mt-8 w-full py-3" @click="router.push('/signin')">
              Back to sign in
            </button>
          </div>
        </template>

        <template v-else>
          <h1 class="text-center text-2xl font-bold text-brand-ink">Forgot password?</h1>
          <p class="mt-2 text-center text-sm text-brand-muted">Enter your email and we'll send you a reset link.</p>

          <div v-if="errorMessage" class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ errorMessage }}
          </div>

          <form class="mt-8 space-y-5" @submit.prevent="handleSubmit">
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-brand-muted" for="email">Email</label>
              <input id="email" v-model="email" type="email" required autocomplete="email" class="input-field" />
              <p v-if="fieldErrors.email" class="mt-1 text-xs text-brand-rose">{{ fieldErrors.email[0] }}</p>
            </div>

            <button type="submit" class="btn-primary w-full py-3" :disabled="authStore.loading">
              {{ authStore.loading ? "Sending..." : "Send reset link" }}
            </button>
          </form>

          <p class="mt-8 text-center text-sm text-brand-muted">
            <RouterLink class="font-bold text-brand-plum hover:text-brand-rose" to="/signin">Back to sign in</RouterLink>
          </p>
        </template>
      </div>
    </section>
  </LandingLayout>
</template>
