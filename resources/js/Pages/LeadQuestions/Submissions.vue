<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  property:         Object,
  headings:         Array,   // [{ id, question }, ...]
  submissions:      Array,   // [{ id, submitted_at, answers: { [qid]: response } }, ...]
  selectedHeadings: Array,   // loaded from your controller
})

//View Property
function goToView(id) {
  router.visit(`/properties/${id}`)
}
// reactive form for saving preferences
const form = useForm({ selected_headings: props.selectedHeadings })

// modal open/close state for preferences
const isPrefModalOpen = ref(false)

// modal open/close state for viewing a submission
const isViewModalOpen = ref(false)
const selectedSubmission = ref(null)

// compute which heading objects are selected
const selectedCols = computed(() =>
  props.headings.filter(h => form.selected_headings.includes(h.id))
)

// preferences modal handlers
function openPrefModal() {
  form.reset()
  isPrefModalOpen.value = true
}
function closePrefModal() {
  isPrefModalOpen.value = false
}
function selectAll() {
  form.selected_headings = props.headings.map(h => h.id)
}
function deselectAll() {
  form.selected_headings = []
}
function savePreferences() {
  form.post(
    route('properties.updateHeadings', { property: props.property.id }),
    { onSuccess: () => { isPrefModalOpen.value = false } }
  )
}

// view‑submission modal handlers
function viewSubmission(sub) {
  selectedSubmission.value = sub
  isViewModalOpen.value = true
}
function closeViewModal() {
  selectedSubmission.value = null
  isViewModalOpen.value = false
}
</script>

<template>
  <Head title="Submissions" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-white">Submissions</h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="p-6 py-12 bg-white shadow sm:rounded-lg">
          <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">{{ props.property.title }} Entries</h1>
            <PrimaryButton 
            class="bg-gray-600 hover:bg-gray-700"
            @click="goToView(props.property.id)"
          >
            Back
          </PrimaryButton>
          </div>
          <div class="flex justify-end mb-2">
            <PrimaryButton @click="openPrefModal">
              Customize Columns
            </PrimaryButton>
          </div>

          <!-- Table -->
          <div v-if="props.submissions.length" class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 rounded-lg">
              <thead class="text-sm text-left text-gray-600 uppercase bg-gray-100">
                <tr>
                  <th class="px-4 py-2">Date</th>
                  <th class="px-4 py-2">Qualified</th>
                  <th v-for="col in selectedCols" :key="col.id" class="px-4 py-2">
                    {{ col.question }}
                  </th>
                  <th class="px-4 py-2">Action</th>
                </tr>
              </thead>
              <tbody class="text-sm text-gray-700">
                <tr
                  v-for="sub in props.submissions"
                  :key="sub.id"
                  :class="sub.qualified ? 'bg-green-50' : 'bg-red-50'"
  class="border-t hover:bg-gray-100"
                >
                  <td class="px-4 py-2">{{ sub.submitted_at }}</td>
                  <td class="px-4 py-2 text-sm font-medium">
                    {{ sub.qualified ? 'Yes' : 'No' }}
                  </td>
                  <td
                    v-for="col in selectedCols"
                    :key="col.id"
                    class="px-4 py-2"
                  >
                    {{ sub.answers[col.id] || '—' }}
                  </td>
                  <td class="px-4 py-2">
                    <PrimaryButton
                      class="bg-gray-600 hover:bg-gray-700"
                      @click="viewSubmission(sub)"
                    >
                      View
                    </PrimaryButton>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <p v-else class="text-gray-500">No submissions found for this property.</p>
        </div>
      </div>
    </div>

    <!-- Preferences Modal -->
    <Modal :show="isPrefModalOpen" @close="closePrefModal">
      <template #header>
        <h3 class="text-lg font-semibold">Select Columns</h3>
      </template>

      <div class="p-4 space-y-4">
        <!-- select/deselect all -->
        <div class="flex mb-4 space-x-2">
          <PrimaryButton
            class="bg-teal-600 hover:bg-teal-700"
            @click="selectAll"
          >
            Select All
          </PrimaryButton>
          <PrimaryButton
            class="bg-gray-600 hover:bg-gray-700"
            @click="deselectAll"
          >
            Deselect All
          </PrimaryButton>
        </div>

        <!-- individual checkboxes -->
        <div v-for="h in props.headings" :key="h.id" class="flex items-center">
          <input
            type="checkbox"
            :value="h.id"
            v-model="form.selected_headings"
            class="mr-2"
          />
          <label>{{ h.question }}</label>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end space-x-2">
          <PrimaryButton
            class="bg-red-500 hover:bg-red-600"
            @click="closePrefModal"
          >
            Cancel
          </PrimaryButton>
          <PrimaryButton
            class="bg-blue-600 hover:bg-blue-700"
            @click="savePreferences"
            :disabled="form.processing"
          >
            Save
          </PrimaryButton>
        </div>
      </template>
    </Modal>

  <!-- View Submission Modal -->
  <Modal :show="isViewModalOpen" @close="closeViewModal">
   <div class="p-5">
      <h3 class="text-lg font-semibold">Submission Details</h3>

    <!-- Only show once selectedSubmission is set -->
    <div v-if="selectedSubmission" class="p-4 space-y-4 overflow-y-auto max-h-96">
      <div
        v-for="h in props.headings"
        :key="h.id"
        class="space-y-1"
      >
        <p class="font-bold text-gray-700">{{ h.question }}</p>
        <p class="text-gray-800">
          {{ selectedSubmission.answers[h.id] ?? '—' }}
        </p>
      </div>
    </div>

      <div class="flex justify-end">
        <PrimaryButton
          class="bg-gray-600 hover:bg-gray-700"
          @click="closeViewModal"
        >
          Close
        </PrimaryButton>
      </div>
    </div>
  </Modal>
  </AuthenticatedLayout>
</template>
