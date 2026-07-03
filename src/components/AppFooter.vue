<script setup>
import BrandLogo from "./BrandLogo.vue";
import { navLinks } from "../data/homeData";
import { tagline } from "../data/siteSeo";

const year = new Date().getFullYear();
const footerLinks = navLinks.filter((link) => link.to !== "/wardrobe");
</script>

<template>
  <footer class="mt-auto border-t border-brand-border bg-white/90 py-12 backdrop-blur-sm">
    <div class="container-shell grid gap-10 md:grid-cols-[1.4fr_1fr_1fr]">
      <div>
        <BrandLogo size="md" />
        <p class="mt-4 text-sm leading-relaxed text-brand-muted">{{ tagline }}</p>
        <p class="mt-3 text-sm leading-relaxed text-brand-muted">
          Digital wardrobe, outfit recommendations, and beautician booking for confident styling
          every day and every event.
        </p>
      </div>

      <div>
        <h4 class="text-sm font-bold uppercase tracking-wider text-brand-plum">Explore</h4>
        <nav class="mt-4 flex flex-col gap-2.5" aria-label="Footer navigation">
          <RouterLink
            v-for="link in footerLinks"
            :key="link.to"
            :to="link.to"
            class="text-sm text-brand-muted transition-colors hover:text-brand-plum"
          >
            {{ link.label }}
          </RouterLink>
        </nav>
      </div>

      <div>
        <h4 class="text-sm font-bold uppercase tracking-wider text-brand-plum">Contact</h4>
        <a
          href="mailto:hello@makemeupai.com"
          class="mt-4 inline-block text-sm text-brand-muted transition-colors hover:text-brand-rose"
        >
          hello@makemeupai.com
        </a>
        <p class="mt-8 text-xs text-brand-muted">© {{ year }} MakemeupAI. All rights reserved.</p>
      </div>
    </div>
  </footer>
</template>
