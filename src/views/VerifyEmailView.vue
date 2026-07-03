<script setup>
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import BrandLogo from "../components/BrandLogo.vue";
import LandingLayout from "../layouts/LandingLayout.vue";
import { authStore } from "../stores/auth";

const route = useRoute();
const router = useRouter();
const status = ref(route.query.status || "pending");

onMounted(async () => {
  if (status.value === "success") {
    await authStore.fetchUser();
    setTimeout(() => {
      router.replace("/dashboard");
    }, 2000);
  }
});

async function handleResend() {
  if (!authStore.isLoggedIn) {
    router.push("/signin");
    return;
  }

  try {
    await authStore.resendVerification();
  } catch {
    // handled on check-email page
  }

  router.push("/check-email");
}
</script>

<template>
  <LandingLayout>
    <section class="container-shell flex min-h-[calc(100vh-200px)] items-center py-16">
      <div class="auth-card text-center">
        <div class="mb-6 flex justify-center">
          <BrandLogo size="lg" :show-text="false" to="/" />
        </div>

        <template v-if="status === 'success'">
          <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-3xl text-green-600">
            ✓
          </div>
          <h1 class="text-2xl font-bold text-brand-ink">Email verified!</h1>
          <p class="mt-3 text-sm text-brand-muted">Your account is ready. Redirecting to your dashboard...</p>
        </template>

        <template v-else-if="status === 'invalid'">
          <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-3xl text-red-600">
            !
          </div>
          <h1 class="text-2xl font-bold text-brand-ink">Link expired or invalid</h1>
          <p class="mt-3 text-sm text-brand-muted">
            This verification link is no longer valid. Request a new one to continue.
          </p>
          <button type="button" class="btn-primary mt-8 w-full py-3" @click="handleResend">
            Resend verification email
          </button>
        </template>

        <template v-else>
          <div class="mx-auto mb-6 h-10 w-10 animate-spin rounded-full border-4 border-brand-line border-t-brand-rose" />
          <h1 class="text-2xl font-bold text-brand-ink">Verifying email...</h1>
          <p class="mt-3 text-sm text-brand-muted">Please wait while we confirm your verification status.</p>
        </template>
      </div>
    </section>
  </LandingLayout>
</template>
