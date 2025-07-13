<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  propertyCount: Number,
});

</script>

<template>
    <Head title="Dashboard" />
  
    <AuthenticatedLayout>
      <template #header>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Welcome, {{ $page.props.auth.user.first_name }}
        </h2>
      </template>
  
      <div class="py-12">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
          <!-- You're logged in message -->
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">You're logged in!</div>
          </div>
  
          <!-- Property Count Card -->
          <div class="overflow-hidden bg-white shadow sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-800">My Properties</h3>
              <p class="mt-1 text-2xl font-bold text-blue-600">
                {{ props.propertyCount }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  </template>
  