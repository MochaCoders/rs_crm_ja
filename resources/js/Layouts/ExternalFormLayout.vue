<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="flex items-center justify-center min-h-screen px-4 bg-gradient-to-br from-gray-100 via-white to-gray-200">
    <div class="w-full max-w-4xl">
      <div class="flex justify-center mb-2">
        <Link href="/" class="transition hover:opacity-80">
          <ApplicationLogo class="w-16 h-16 text-indigo-600" />
        </Link>
      </div>

      <div class="p-8 bg-white shadow-lg rounded-xl">
        <slot />
      </div>
    </div>
  </div>
</template>
