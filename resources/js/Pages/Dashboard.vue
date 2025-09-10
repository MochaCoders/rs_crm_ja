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
        <h2 class="text-xl font-semibold leading-tight text-white">
          Welcome {{ $page.props.auth.user.first_name }}
        </h2>
      </template>
  
      <div class="py-12">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">  
          <!-- Property Count Card -->
          <div class="overflow-hidden bg-blue-800 shadow sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-200">My Properties</h3>
              <p class="mt-1 text-2xl font-bold text-blue-200">
                {{ props.propertyCount }}
              </p>
            </div>
          </div>
        </div>
        <div class="mt-5 text-center text-blue-900">
          <p>The RS Automation tool does not take personal credit card information.</p>
        </div>
      </div>
    </AuthenticatedLayout>
  </template>
  