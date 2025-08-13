<script setup>
import { ref } from 'vue'
import { useForm, Head, router } from '@inertiajs/vue3'
import ExternalFormLayout from '@/Layouts/ExternalFormLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

// Date/time picker
import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

// Props from controller
const props = defineProps({
  property: { type: Object, required: true }, // { id, title }
  email:    { type: String, default: '' },
})

// Form state
const form = useForm({
  email: props.email || '',
  scheduled_at: null,     // will hold a JS Date
})

// Submit
function submit() {
  // Convert to ISO string (UTC) for the backend
  const payload = {
    email: form.email,
    scheduled_at: form.scheduled_at
      ? new Date(form.scheduled_at).toISOString()
      : null,
  }

  form.transform(() => payload).post(
    route('appointments.store', { property: props.property.id }),
    {
      onSuccess: () => {
        // keep simple; you can redirect or show toast. Inertia flash 'success' available.
      },
    }
  )
}
</script>

<template>
  <ExternalFormLayout>
    <Head title="Set Appointment" />
    <div class="max-w-5xl px-6 py-10 mx-auto rounded-2xl">
      <h1 class="mb-2 text-3xl font-bold text-gray-800">Appointment</h1>
      <p class="mb-8 text-gray-600">Property: <strong>{{ props.property.title }}</strong></p>

      <div class="max-w-xl p-6 bg-white border rounded-2xl">
        <form @submit.prevent="submit" class="space-y-5">
          <!-- Email -->
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">Email</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="w-full p-2 border rounded"
              placeholder="you@example.com"
            />
            <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
              {{ form.errors.email }}
            </div>
          </div>

          <!-- Date & Time -->
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">
              Pick a date & time
            </label>
            <VueDatePicker
              v-model="form.scheduled_at"
              :min-date="new Date()"
              :enable-time-picker="true"
              :minutes-increment="15"
              :is-24="false"
              placeholder="Select date and time"
              class="w-full"
            />
            <div v-if="form.errors.scheduled_at" class="mt-1 text-sm text-red-600">
              {{ form.errors.scheduled_at }}
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-2">
            <PrimaryButton type="submit" class="bg-blue-600 hover:bg-blue-700">
              Schedule Visit
            </PrimaryButton>
          </div>
        </form>
      </div>

      <!-- Optional success flash -->
      <div
        v-if="$page.props.flash && $page.props.flash.success"
        class="max-w-xl p-4 mt-6 text-green-800 bg-green-100 border border-green-200 rounded"
      >
        {{ $page.props.flash.success }}
      </div>
    </div>
  </ExternalFormLayout>
</template>
