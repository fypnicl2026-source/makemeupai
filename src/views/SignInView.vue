<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import BrandLogo from "../components/BrandLogo.vue";
import LandingLayout from "../layouts/LandingLayout.vue";
import { authStore } from "../stores/auth";

const router = useRouter();

const email = ref("");
const password = ref("");
const remember = ref(false);
const errorMessage = ref("");
const fieldErrors = ref({});

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

  if (password.value.length < 8) {
    errorMessage.value = "Password must be at least 8 characters.";
    return;
  }

  try {
    await authStore.login(email.value, password.value, remember.value);
    if (authStore.isLoggedIn) {
      router.push(authStore.emailVerified ? "/dashboard" : "/check-email");
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || "Unable to sign in. Please try again.";
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
        <h1 class="text-center text-2xl font-bold text-brand-ink">Welcome back</h1>
        <p class="mt-2 text-center text-sm text-brand-muted">Sign in to your MakemeupAI account</p>

        <div
          v-if="errorMessage"
          class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
        >
          {{ errorMessage }}
        </div>

        <form class="mt-8 space-y-5" @submit.prevent="handleSubmit">
          <div>
            <label class="mb-1.5 block text-sm font-semibold text-brand-muted" for="email">Email</label>
            <input id="email" v-model="email" type="email" required autocomplete="email" class="input-field" />
            <p v-if="fieldErrors.email" class="mt-1 text-xs text-brand-rose">{{ fieldErrors.email[0] }}</p>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-semibold text-brand-muted" for="password">Password</label>
            <input id="password" v-model="password" type="password" required autocomplete="current-password" class="input-field" />
            <p v-if="fieldErrors.password" class="mt-1 text-xs text-brand-rose">{{ fieldErrors.password[0] }}</p>
          </div>

          <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-brand-muted">
              <input v-model="remember" type="checkbox" class="rounded border-brand-border text-brand-rose focus:ring-brand-rose/30" />
              Remember me
            </label>
            <RouterLink class="font-semibold text-brand-plum hover:text-brand-rose" to="/forgot-password">
              Forgot password?
            </RouterLink>
          </div>

          <button type="submit" class="btn-primary w-full py-3" :disabled="authStore.loading">
            {{ authStore.loading ? "Signing in..." : "Sign In" }}
          </button>
        </form>

        <p class="mt-8 text-center text-sm text-brand-muted">
          Don't have an account?
          <RouterLink class="font-bold text-brand-plum hover:text-brand-rose" to="/signup">Sign up</RouterLink>
        </p>
      </div>
    </section>
  </LandingLayout>
</template>
