<script setup>
import { useRouter } from "vue-router";
import BrandLogo from "../components/BrandLogo.vue";
import { authStore } from "../stores/auth";

const router = useRouter();

const navLinks = [
  { label: "Dashboard", to: "/dashboard", icon: "◆" },
  { label: "Face Insights", to: "/face-insights", icon: "◇" },
  { label: "Wardrobe", to: "/wardrobe", icon: "▣" },
  { label: "Recommendations", to: "/recommendations", icon: "✦" },
  { label: "Bookings", to: "/bookings", icon: "◎" },
];

function initials(name) {
  if (!name) return "?";
  return name
    .split(" ")
    .map((part) => part[0])
    .join("")
    .slice(0, 2)
    .toUpperCase();
}

async function handleSignOut() {
  await authStore.logout();
  router.push("/");
}
</script>

<template>
  <div class="min-h-screen bg-brand-blush">
    <div class="flex min-h-screen flex-col md:flex-row">
      <aside class="hidden w-64 shrink-0 border-r border-brand-line bg-white/90 p-6 backdrop-blur-md md:block">
        <BrandLogo size="sm" to="/dashboard" class="mb-8" />

        <div class="mb-8 flex items-center gap-3 rounded-2xl border border-brand-line bg-brand-blush-deep/50 p-3">
          <img
            v-if="authStore.user?.profile_photo_url"
            :src="authStore.user.profile_photo_url"
            :alt="authStore.user.name"
            class="h-11 w-11 rounded-full object-cover ring-2 ring-white shadow-soft"
          />
          <div
            v-else
            class="flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-br from-brand-rose to-brand-lilac text-sm font-bold text-white shadow-soft"
          >
            {{ initials(authStore.user?.name) }}
          </div>
          <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-brand-ink">{{ authStore.user?.name }}</p>
            <p class="truncate text-xs text-brand-muted">{{ authStore.user?.city }}</p>
          </div>
        </div>

        <nav class="space-y-1">
          <RouterLink
            v-for="link in navLinks"
            :key="link.to"
            :to="link.to"
            class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm text-brand-muted transition-all hover:bg-brand-blush-deep hover:text-brand-plum"
            active-class="!bg-gradient-to-r !from-brand-blush-deep !to-white !font-semibold !text-brand-plum !shadow-soft"
          >
            <span class="text-xs opacity-60">{{ link.icon }}</span>
            {{ link.label }}
          </RouterLink>
          <button
            type="button"
            class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-left text-sm text-brand-muted transition-all hover:bg-red-50 hover:text-red-700"
            @click="handleSignOut"
          >
            <span class="text-xs opacity-60">↪</span>
            Sign Out
          </button>
        </nav>
      </aside>

      <div class="flex flex-1 flex-col">
        <header class="border-b border-brand-line bg-white/90 p-4 backdrop-blur-md md:hidden">
          <BrandLogo size="sm" to="/dashboard" class="mb-3" />
          <nav class="flex gap-2 overflow-x-auto pb-1">
            <RouterLink
              v-for="link in navLinks"
              :key="`mobile-${link.to}`"
              :to="link.to"
              class="whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-medium text-brand-muted ring-1 ring-brand-line transition-all"
              active-class="!bg-brand-blush-deep !font-semibold !text-brand-plum !ring-brand-rose/40"
            >
              {{ link.label }}
            </RouterLink>
            <button
              type="button"
              class="whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-medium text-brand-muted ring-1 ring-brand-line"
              @click="handleSignOut"
            >
              Sign Out
            </button>
          </nav>
        </header>

        <main class="flex-1 p-4 md:p-8">
          <slot />
        </main>
      </div>
    </div>
  </div>
</template>
