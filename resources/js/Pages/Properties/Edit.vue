<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue';
import { useForm, Head, router } from '@inertiajs/vue3'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import Select from '@/Components/Select.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Modal from '@/Components/Modal.vue'

const fileInput = ref(null);
const uploadProgress = ref(null)
const isDeleteModalOpen = ref(false)
const unitToDelete = ref(null)
const isCloseModalOpen = ref(false)
const isReopenModalOpen = ref(false)
const unitToClose = ref(null)
const unitToRepen = ref(null)

const props = defineProps({
  property: Object,
  prospects: Array,
  has_form: Boolean,
  has_entries: Boolean
})

const form = useForm({
  title: props.property.data.title,
  description: props.property.data.description,
  address: props.property.data.address,
  parish: props.property.data.parish,
})

function submit() {
  form.put(route('properties.update', props.property.data.id));
}

function triggerFileInput() {
  fileInput.value.click();
}

function handleFileUpload(event) {
  const file = event.target.files[0]
  if (!file) return

  const formData = new FormData()
  formData.append('file', file)
  formData.append('property_id', props.property.data.id)

  router.post(route('units.upload'), formData, {
    forceFormData: true,
    onStart: () => {
      uploadProgress.value = 0
    },
    onProgress: (event) => {
      uploadProgress.value = event.percentage
    },
    onSuccess: () => {
      uploadProgress.value = null
    },
    onError: () => {
      uploadProgress.value = null
    },
  })
}

//delete
function openDeleteModal(unitId) {
  unitToDelete.value = unitId
  isDeleteModalOpen.value = true
}

function closeDeleteModal() {
  unitToDelete.value = null
  isDeleteModalOpen.value = false
}

function confirmDeleteUnit() {
  if (!unitToDelete.value) return

  router.delete(route('units.destroy', unitToDelete.value), {
    onSuccess: () => {
      closeDeleteModal()
    },
  })
}

//Select Purchaser
// Open modal and store unit
function openCloseModal(unitId) {
  unitToClose.value = unitId
  isCloseModalOpen.value = true
}

function closeCloseModal() {
  unitToClose.value = null
  isCloseModalOpen.value = false
}

// When prospect is selected for closing
function selectProspect(prospectId) {
  router.post(route('units.close'), {
    unit_id: unitToClose.value,
    submission_id: prospectId,
  }, {
    onSuccess: () => {
      closeCloseModal()
    },
  })
}

//Select Purchaser
function openReopenModal(unitId) {
  unitToRepen.value = unitId
  isReopenModalOpen.value = true
}

function closeReopenModal() {
  unitToRepen.value = null
  isReopenModalOpen.value = false
}

function reopenUnit() {
  if (!unitToRepen.value) return

  router.post(route('units.reopen'), {
    unit_id: unitToRepen.value,
  }, {
    onSuccess: () => {
      closeReopenModal()
    },
  })
}

// Turn a LeadResponse into "Question: Answer" pieces
function responseLabel(resp) {
  // If you eager-loaded: Submission::with('responses.question'), this will show the text
  return resp?.question?.question ?? `Question #${resp.lead_question_id}`;
}

function responseValue(resp) {
  // Handle common column names and array/json answers
  const raw =
    resp?.answer ??         // if your column is 'answer'
    resp?.value ??          // or 'value'
    resp?.response ??       // or 'response'
    resp?.text ??           // or 'text'
    '';

  if (Array.isArray(raw)) return raw.join(', ');

  if (typeof raw === 'string') {
    // If checkboxes were stored as JSON strings like '["A","B"]'
    try {
      const parsed = JSON.parse(raw);
      if (Array.isArray(parsed)) return parsed.join(', ');
      if (parsed && typeof parsed === 'object') return Object.values(parsed).join(', ');
    } catch (e) {
      // not JSON — fall through
    }
  }

  return String(raw ?? '');
}
</script>

<template>
<Head title="My Properties" />

<AuthenticatedLayout>
  <template #header>
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        My Properties
      </h2>
    </div>
  </template>
  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
    <div class="p-6 py-12 bg-white shadow sm:rounded-lg">
      <h1>Manage Property</h1>
      <form @submit.prevent="submit" class="mt-5 space-y-4">
    <TextInput v-model="form.title" placeholder="Title" class="w-full" />
    <TextArea v-model="form.description" placeholder="Description" rows="2" class="w-full" />
    <TextInput v-model="form.address" placeholder="Address" class="w-full" />
    <Select
      v-model="form.parish"
      :options="[
        { label: 'Kingston', value: 'Kingston' },
        { label: 'St Andrew', value: 'St Andrew' },
        { label: 'Clarendon', value: 'Clarendon' },
        { label: 'Hanover', value: 'Hanover' },
        { label: 'St Elizabeth', value: 'St Elizabeth' },
        { label: 'St Thomas', value: 'St Thomas' },
        { label: 'St James', value: 'St James' },
        { label: 'St Ann', value: 'St Ann' },
        { label: 'St Catherine', value: 'St Catherine' },
        { label: 'Westmoreland', value: 'Westmoreland' },
        { label: 'Manchester', value: 'Manchester' },
        { label: 'St Mary', value: 'St Mary' },
        { label: 'Trelawny', value: 'Trelawny' },
      ]"
    />
    <div class="flex mt-6">
      <!-- Left-aligned update button -->
      <PrimaryButton type="submit" class="bg-purple-600 hover:bg-purple-700">
        Update Property
      </PrimaryButton>
    </div>
    </form>

    <div class="mt-6">
      <h1>Lead Management</h1>
      <p>Manage lead qualification for {{ form.title }}</p>
      <PrimaryButton
        class="mt-2 bg-teal-600 hover:bg-teal-700" @click="router.get(route('lead-questions.index', { property_id: props.property.data.id }))">
        <span>
          <span v-if="has_form">Update</span><span v-else>Add</span>  Lead Form
        </span>
      </PrimaryButton>
    </div>
    <div class="mt-6" v-if="has_entries">
      <h1>Entries</h1>
      <p>View entries to your lead form</p>
      <PrimaryButton
        class="mt-2 bg-teal-600 hover:bg-teal-700" @click="router.get(route('lead-form.submissions', { property: props.property.data }))">
View Entries
      </PrimaryButton>
       </div>
       <div class="mt-6" v-else>
        <h2 class="text-gray-400">No Entries have been submitted for this form.</h2>
       </div>
    <div class="flex items-center justify-between mt-6">
      <h1>Manage Units</h1>
      <!-- Right-aligned upload units button -->
      <PrimaryButton @click="triggerFileInput" class="bg-teal-600 hover:bg-teal-700">
        Upload Units
      </PrimaryButton>
    </div>
<!-- Units Table -->
<div class="mt-6 overflow-x-auto ">
  <!-- Hidden file input -->
  <input
      type="file"
      ref="fileInput"
      class="hidden"
      @change="handleFileUpload"
      accept=".csv, .xlsx"
    />
    <div v-if="uploadProgress !== null" class="w-48 h-2 mt-2 bg-gray-200 rounded">
      <div
        class="h-2 transition-all duration-300 bg-teal-600 rounded"
        :style="{ width: uploadProgress + '%' }"
      ></div>
    </div>
  <table class="min-w-full text-sm text-left text-gray-700 border border-gray-200 rounded-lg">
    <thead class="text-xs text-gray-600 uppercase bg-gray-100">
      <tr>
        <th class="px-4 py-2">Unit #</th>
        <th class="px-4 py-2">Description</th>
        <th class="px-4 py-2">Price</th>
        <th class="px-4 py-2">Type</th>
        <th class="px-4 py-2">Status</th>
        <th class="px-4 py-2">Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="unit in property.data.units" :key="unit.id" class="border-t">
        <td class="px-4 py-2">{{ unit.unit_number }}</td>
        <td class="px-4 py-2">{{ unit.description }}</td>
        <td class="px-4 py-2">{{ unit.price }} {{ unit.currency }}</td>
        <td class="px-4 py-2">{{ unit.type }}</td>
        <td class="px-4 py-2">
  <span :class="unit.status === 'Sold' ? 'p-2 bg-green-100 text-green-700 font-semibold rounded-lg' : 'p-2 bg-amber-100 text-amber-700 font-semibold rounded-lg'"
  >{{ unit.status }}</span>
</td>
        <td class="px-4 py-2">
          <PrimaryButton class="text-white bg-cyan-700" @click="openCloseModal(unit.id)" v-if="!unit.submission_id">Close</PrimaryButton>
          <PrimaryButton class="text-white bg-amber-800" @click="openReopenModal(unit.id)" v-else>Reopen</PrimaryButton>
          <PrimaryButton class="ml-2 text-white bg-red-700" @click="openDeleteModal(unit.id)" v-if="!unit.purchaser_id">X</PrimaryButton>
        </td>
      </tr>
      <tr v-if="!property.data.units.length">
        <td colspan="6" class="px-4 py-4 text-center text-gray-500">
          No units added yet.
        </td>
      </tr>
    </tbody>
  </table>
</div>
    </div>
  </div>
  </div>
  <Modal :show="isDeleteModalOpen" @close="closeDeleteModal">
  <div class="p-6">
    <h2 class="text-lg font-semibold text-gray-800">Delete Unit</h2>
    <p class="mt-2 text-sm text-gray-600">
      Are you sure you want to delete this unit? This action cannot be undone.
    </p>
    <div class="flex justify-end mt-6 space-x-4">
      <PrimaryButton class="bg-gray-400 hover:bg-gray-500" @click="closeDeleteModal">
        Cancel
      </PrimaryButton>
      <PrimaryButton class="bg-red-600 hover:bg-red-700" @click="confirmDeleteUnit">
        Yes, Delete
      </PrimaryButton>
    </div>
  </div>
</Modal>
<Modal :show="isCloseModalOpen" @close="closeCloseModal">
  <div class="p-6">
    <h2 class="text-lg font-semibold text-gray-800">Select Purchaser</h2>
    <p class="mb-4 text-sm text-gray-600">Choose a prospect to assign as purchaser for the unit.</p>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm text-left text-gray-700 border border-gray-200 rounded-lg">
        <thead class="text-xs text-gray-600 uppercase bg-gray-100">
          <tr>
            <th class="px-4 py-2">Details</th>
            <th class="px-4 py-2">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="prospect in prospects" :key="prospect.id" class="align-top border-t">
            <td class="px-4 py-2">
              <ul class="space-y-1">
                <li
                  v-for="resp in prospect.responses || []"
                  :key="resp.id"
                >
                  <span class="font-medium">{{ responseLabel(resp) }}:</span>
                  <span> {{ responseValue(resp) }} </span>
                </li>
              </ul>
            </td>
            <td class="px-4 py-2">
              <PrimaryButton
                class="text-white bg-green-600 hover:bg-green-700"
                @click="selectProspect(prospect.id)"
              >
                Select
              </PrimaryButton>
            </td>
          </tr>

          <tr v-if="!prospects?.length">
            <td colspan="2" class="px-4 py-4 text-center text-gray-500">
              No prospects found.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="flex justify-end mt-4">
      <PrimaryButton class="bg-gray-400 hover:bg-gray-500" @click="closeCloseModal">
        Cancel
      </PrimaryButton>
    </div>
  </div>
</Modal>
<!-- Reopen Modal -->
<Modal :show="isReopenModalOpen" @close="closeReopenModal">
  <div class="p-6">
    <h2 class="text-lg font-semibold text-gray-800">Reopen Unit</h2>
    <p class="mb-4 text-sm text-gray-600">Do you want to remove the assigned purchaser for the unit?</p>

    <div class="flex justify-end mt-4">
      <PrimaryButton class="bg-gray-400 hover:bg-gray-500" @click="reopenUnit()">
        Yes
      </PrimaryButton>
      <PrimaryButton class="ml-2 bg-red-600 hover:bg-red-700" @click="closeCloseModal">
        Cancel
      </PrimaryButton>
    </div>
  </div>
</Modal>
  </AuthenticatedLayout>
</template>