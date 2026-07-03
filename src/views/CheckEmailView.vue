<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { useRouter } from "vue-router";
import BrandLogo from "../components/BrandLogo.vue";
import LandingLayout from "../layouts/LandingLayout.vue";
import { authStore } from "../stores/auth";

const router = useRouter();
const cooldown = ref(0);
const message = ref("");
const errorMessage = ref("");
let timer = null;

const email = computed(() => authStore.user?.email ?? "");

function startCooldown(seconds = 60) {
  cooldown.value = seconds;
  clearInterval(timer);
  timer = setInterval(() => {
    cooldown.value -= 1;
    if (cooldown.value <= 0) {
      clearInterval(timer);
      timer = null;
    }
  }, 1000);
}

async function handleResend() {
  errorMessage.value = "";
  message.value = "";

  try {
    await authStore.resendVerification();
    message.value = "Verification email sent. Please check your inbox.";
    startCooldown(60);
  } catch (error) {
    const waitMatch = error.response?.data?.message?.match(/(\d+) seconds/);
    if (error.response?.status === 429 && waitMatch) {
      startCooldown(Number(waitMatch[1]));
    }
    errorMessage.value = error.response?.data?.message || "Unable to resend email. Please try again.";
  }
}

onMounted(() => {
  if (!authStore.isLoggedIn) {
    router.replace("/signin");
    return;
  }

  if (authStore.emailVerified) {
    router.replace("/dashboard");
  }
});

onUnmounted(() => {
  clearInterval(timer);
});
</script>

<template>
  <LandingLayout>
    <section class="container-shell flex min-h-[calc(100vh-200px)] items-center py-16">
      <div class="auth-card text-center">
        <div class="mb-6 flex justify-center">
          <BrandLogo size="lg" :show-text="false" to="/" />
        </div>
        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-brand-blush-deep text-3xl">
          ✉
        </div>
        <h1 class="text-2xl font-bold text-brand-ink">Check your email</h1>
        <p class="mt-3 text-sm leading-relaxed text-brand-muted">
          We sent a verification link to
          <span class="font-bold text-brand-plum">{{ email }}</span>.
          Click the link to verify your account.
        </p>

        <div v-if="message" class="mt-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
          {{ message }}
        </div>

        <div v-if="errorMessage" class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
          {{ errorMessage }}
        </div>

        <button
          type="button"
          class="btn-primary mt-8 w-full py-3"
          :disabled="authStore.loading || cooldown > 0"
          @click="handleResend"
        >
          {{
            cooldown > 0
              ? `Resend available in ${cooldown}s`
              : authStore.loading
                ? "Sending..."
                : "Resend verification email"
          }}
        </button>
      </div>
    </section>
  </LandingLayout>
</template>
