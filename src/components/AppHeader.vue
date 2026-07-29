<script setup>
import { onMounted, onUnmounted, ref } from "vue";
import BrandLogo from "./BrandLogo.vue";
import { authStore } from "../stores/auth";

defineProps({
  links: {
    type: Array,
    required: true,
  },
});

const isMobileMenuOpen = ref(false);
const isScrolled = ref(false);

function onScroll() {
  isScrolled.value = window.scrollY > 8;
}

onMounted(() => window.addEventListener("scroll", onScroll, { passive: true }));
onUnmounted(() => window.removeEventListener("scroll", onScroll));
</script>

<template>
  <header
    :class="[
      'sticky top-0 z-50 border-b transition-all duration-300',
      isScrolled
        ? 'border-brand-line/70 bg-white/90 shadow-soft backdrop-blur-md'
        : 'border-transparent bg-transparent backdrop-blur-sm',
    ]"
  >
    <nav class="container-shell flex min-h-[72px] items-center justify-between gap-4">
      <BrandLogo size="md" />

      <button
        type="button"
        class="rounded-xl border border-brand-line/80 bg-white/80 px-3 py-2 text-sm font-semibold text-brand-plum md:hidden"
        aria-label="Toggle menu"
        @click="isMobileMenuOpen = !isMobileMenuOpen"
      >
        {{ isMobileMenuOpen ? "Close" : "Menu" }}
      </button>

      <ul class="hidden items-center gap-0.5 md:flex">
        <li v-for="link in links" :key="link.to">
          <RouterLink
            :to="link.to"
            class="nav-link"
            active-class="nav-link-active"
          >
            {{ link.label }}
          </RouterLink>
        </li>
      </ul>

      <div class="hidden items-center gap-2 md:flex">
        <RouterLink v-if="authStore.isLoggedIn" class="btn-primary" to="/dashboard">
          Dashboard
        </RouterLink>
        <template v-else>
          <RouterLink class="nav-link px-3" to="/signin">Sign in</RouterLink>
          <RouterLink class="btn-primary" to="/signup">Get started</RouterLink>
        </template>
      </div>
    </nav>

    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div
        v-if="isMobileMenuOpen"
        class="container-shell mb-4 flex flex-col gap-1 rounded-2xl border border-brand-line bg-white p-3 shadow-elevated md:hidden"
      >
        <RouterLink
          v-for="link in links"
          :key="`mobile-${link.to}`"
          :to="link.to"
          class="nav-link"
          active-class="nav-link-active"
          @click="isMobileMenuOpen = false"
        >
          {{ link.label }}
        </RouterLink>
        <div class="mt-2 flex flex-col gap-2 border-t border-brand-line pt-3">
          <RouterLink
            v-if="authStore.isLoggedIn"
            class="btn-primary text-center"
            to="/dashboard"
            @click="isMobileMenuOpen = false"
          >
            Dashboard
          </RouterLink>
          <template v-else>
            <RouterLink class="btn-ghost text-center" to="/signin" @click="isMobileMenuOpen = false">
              Sign In
            </RouterLink>
            <RouterLink class="btn-primary text-center" to="/signup" @click="isMobileMenuOpen = false">
              Get Started
            </RouterLink>
          </template>
        </div>
      </div>
    </Transition>
  </header>
</template>
