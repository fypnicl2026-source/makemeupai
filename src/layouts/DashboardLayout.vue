<script setup>
import { useRouter } from "vue-router";
import BrandLogo from "../components/BrandLogo.vue";
import { authStore } from "../stores/auth";

const router = useRouter();

const navLinks = [
  {
    label: "Dashboard",
    to: "/dashboard",
    icon: "M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z",
  },
  {
    label: "Face Insights",
    to: "/face-insights",
    icon: "M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    label: "Wardrobe",
    to: "/wardrobe",
    icon: "M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10",
  },
  {
    label: "Recommendations",
    to: "/recommendations",
    icon: "M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z",
  },
  {
    label: "Bookings",
    to: "/bookings",
    icon: "M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z",
  },
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
      <aside class="hidden w-60 shrink-0 border-r border-brand-line/80 bg-white/85 p-5 backdrop-blur-sm md:block">
        <BrandLogo size="sm" to="/dashboard" class="mb-7" />

        <div class="mb-7 flex items-center gap-3 rounded-2xl border border-brand-line/70 bg-brand-blush-deep/40 p-3">
          <img
            v-if="authStore.user?.profile_photo_url"
            :src="authStore.user.profile_photo_url"
            :alt="authStore.user.name"
            class="h-10 w-10 rounded-full object-cover ring-2 ring-white shadow-soft"
          />
          <div
            v-else
            class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-brand-plum to-brand-rose text-sm font-semibold text-white shadow-soft"
          >
            {{ initials(authStore.user?.name) }}
          </div>
          <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-brand-ink">{{ authStore.user?.name }}</p>
            <p class="truncate text-xs text-brand-muted">{{ authStore.user?.city }}</p>
          </div>
        </div>

        <nav class="space-y-0.5">
          <RouterLink
            v-for="link in navLinks"
            :key="link.to"
            :to="link.to"
            class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm text-brand-muted transition-all hover:bg-brand-blush-deep/70 hover:text-brand-plum"
            active-class="!bg-brand-blush-deep !font-semibold !text-brand-plum !shadow-soft"
          >
            <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
              <path stroke-linecap="round" stroke-linejoin="round" :d="link.icon" />
            </svg>
            {{ link.label }}
          </RouterLink>
          <button
            type="button"
            class="mt-2 flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-left text-sm text-brand-muted transition-all hover:bg-red-50 hover:text-red-700"
            @click="handleSignOut"
          >
            <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Sign out
          </button>
        </nav>
      </aside>

      <div class="flex flex-1 flex-col">
        <header class="border-b border-brand-line/80 bg-white/85 p-4 backdrop-blur-sm md:hidden">
          <BrandLogo size="sm" to="/dashboard" class="mb-3" />
          <nav class="flex gap-1.5 overflow-x-auto pb-1">
            <RouterLink
              v-for="link in navLinks"
              :key="`mobile-${link.to}`"
              :to="link.to"
              class="whitespace-nowrap rounded-lg px-3 py-1.5 text-xs font-medium text-brand-muted ring-1 ring-brand-line/80 transition-all"
              active-class="!bg-brand-blush-deep !font-semibold !text-brand-plum !ring-brand-rose/30"
            >
              {{ link.label }}
            </RouterLink>
            <button
              type="button"
              class="whitespace-nowrap rounded-lg px-3 py-1.5 text-xs font-medium text-brand-muted ring-1 ring-brand-line/80"
              @click="handleSignOut"
            >
              Sign out
            </button>
          </nav>
        </header>

        <main class="page-enter flex-1 p-4 md:p-8">
          <slot />
        </main>
      </div>
    </div>
  </div>
</template>
