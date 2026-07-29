<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import BrandLogo from "../components/BrandLogo.vue";
import LandingLayout from "../layouts/LandingLayout.vue";
import { authStore } from "../stores/auth";

const router = useRouter();

const name = ref("");
const email = ref("");
const password = ref("");
const confirmPassword = ref("");
const city = ref("Lahore");
const gender = ref("");
const errorMessage = ref("");
const fieldErrors = ref({});

function isValidEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
}

async function handleSubmit() {
  errorMessage.value = "";
  fieldErrors.value = {};

  if (!name.value.trim()) {
    errorMessage.value = "Name is required.";
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
    await authStore.register(
      name.value,
      email.value,
      password.value,
      confirmPassword.value,
      city.value,
      gender.value || undefined
    );
    if (authStore.isLoggedIn) {
      router.push("/check-email");
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || "Unable to create account. Please try again.";
    fieldErrors.value = error.response?.data?.errors || {};
  }
}
</script>

<template>
  <LandingLayout>
    <section class="container-shell flex min-h-[calc(100vh-200px)] items-center py-14 md:py-20">
      <div class="auth-card">
        <div class="mb-6 flex justify-center">
          <BrandLogo size="lg" :show-text="false" to="/" />
        </div>
        <h1 class="text-center font-display text-2xl font-semibold text-brand-ink md:text-[1.75rem]">
          Create your account
        </h1>
        <p class="mt-2 text-center text-sm leading-relaxed text-brand-muted">
          Join MakemeupAI and start building your look
        </p>

        <div
          v-if="errorMessage"
          class="mt-6 rounded-xl border border-red-200/80 bg-red-50/90 px-4 py-3 text-sm text-red-800"
        >
          {{ errorMessage }}
        </div>

        <form class="mt-8 space-y-4" @submit.prevent="handleSubmit">
          <div>
            <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="name">Name</label>
            <input id="name" v-model="name" type="text" required autocomplete="name" class="input-field" />
            <p v-if="fieldErrors.name" class="mt-1 text-xs text-brand-rose">{{ fieldErrors.name[0] }}</p>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="email">Email</label>
            <input id="email" v-model="email" type="email" required autocomplete="email" class="input-field" />
            <p v-if="fieldErrors.email" class="mt-1 text-xs text-brand-rose">{{ fieldErrors.email[0] }}</p>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="password">Password</label>
            <input id="password" v-model="password" type="password" required minlength="8" autocomplete="new-password" class="input-field" />
            <p v-if="fieldErrors.password" class="mt-1 text-xs text-brand-rose">{{ fieldErrors.password[0] }}</p>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="confirmPassword">Confirm password</label>
            <input id="confirmPassword" v-model="confirmPassword" type="password" required minlength="8" autocomplete="new-password" class="input-field" />
            <p v-if="fieldErrors.password_confirmation" class="mt-1 text-xs text-brand-rose">{{ fieldErrors.password_confirmation[0] }}</p>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-brand-muted" for="city">City</label>
            <input id="city" v-model="city" type="text" required autocomplete="address-level2" class="input-field" />
            <p v-if="fieldErrors.city" class="mt-1 text-xs text-brand-rose">{{ fieldErrors.city[0] }}</p>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-brand-muted">Gender (optional)</label>
            <div class="segment-group w-full">
              <button
                type="button"
                class="segment-btn flex-1"
                :class="gender === 'female' ? 'segment-btn-active' : ''"
                @click="gender = gender === 'female' ? '' : 'female'"
              >
                Female
              </button>
              <button
                type="button"
                class="segment-btn flex-1"
                :class="gender === 'male' ? 'segment-btn-active' : ''"
                @click="gender = gender === 'male' ? '' : 'male'"
              >
                Male
              </button>
            </div>
            <p class="mt-1.5 text-xs leading-relaxed text-brand-muted">
              Helps personalize looks and salons. You can also set this in Face Insights.
            </p>
            <p v-if="fieldErrors.gender" class="mt-1 text-xs text-brand-rose">{{ fieldErrors.gender[0] }}</p>
          </div>

          <button type="submit" class="btn-primary mt-2 w-full py-3" :disabled="authStore.loading">
            {{ authStore.loading ? "Creating account…" : "Sign up" }}
          </button>
        </form>

        <p class="mt-8 text-center text-sm text-brand-muted">
          Already have an account?
          <RouterLink class="font-semibold text-brand-plum transition-colors hover:text-brand-rose" to="/signin">
            Sign in
          </RouterLink>
        </p>
      </div>
    </section>
  </LandingLayout>
</template>
