<script setup>
import ExternalFormLayout from '@/Layouts/ExternalFormLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { reactive } from 'vue'

const form = reactive({
  propertyId: '',
  email: ''
})

function goSchedule(propertyId, email) {
  router.visit(route('appointments.create', { property: propertyId, email }))
}

function submit() {
  if (!form.propertyId || !form.email) return
  goSchedule(form.propertyId, form.email)
}
</script>

<template>
  <ExternalFormLayout>
    <Head title="Set Appointment" />
    <div class="max-w-5xl px-6 py-10 mx-auto rounded-2xl">
      <h1 class="mb-2 text-3xl font-bold text-gray-800">Create a New Appointment{{  }}</h1>

      <div class="max-w-xl p-6 mt-6 bg-white border rounded-2xl">
        <form @submit.prevent="submit" class="space-y-5">
          <!-- Property ID -->
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">Property ID</label>
            <input
              v-model.trim="form.propertyId"
              type="number"
              min="1"
              required
              class="w-full p-2 border rounded"
              placeholder="Enter the property ID"
            />
          </div>

          <!-- Email -->
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">Email</label>
            <input
              v-model.trim="form.email"
              type="email"
              required
              class="w-full p-2 border rounded"
              placeholder="you@example.com"
            />
          </div>

          <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
              Continue to Scheduling
            </button>
          </div>
        </form>
      </div>
    </div>
  </ExternalFormLayout>
</template>
