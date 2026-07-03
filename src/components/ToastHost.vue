<script setup>
import { useToast } from "../composables/useToast";

const { toast } = useToast();
</script>

<template>
  <div
    v-if="toast.visible"
    class="fixed left-1/2 top-6 z-[100] -translate-x-1/2 rounded-xl px-6 py-3 text-sm font-semibold shadow-elevated backdrop-blur-sm"
    :class="
      toast.type === 'success'
        ? 'border border-green-200 bg-green-50 text-green-800'
        : 'border border-red-200 bg-red-50 text-red-800'
    "
  >
    {{ toast.message }}
  </div>
</template>
