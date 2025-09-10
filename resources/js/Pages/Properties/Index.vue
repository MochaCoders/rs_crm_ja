<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Modal from '@/Components/Modal.vue'
import CreatePropertyForm from '@/Pages/Properties/Create.vue' // This is your existing form

const props = defineProps({
  properties: Array,
})

const isModalOpen = ref(false)
const isDeleteModalOpen = ref(false)
const propertyToDelete = ref(null)
// Share modal state
const isShareModalOpen = ref(false)
const shareLink = ref(null)

function openModal() {
  isModalOpen.value = true
}

function closeModal() {
  isModalOpen.value = false
}

//View Property
function goToView(id) {
  router.visit(`/properties/${id}`)
}

//Delete functions and modals
function goToDelete(id) {
  propertyToDelete.value = id
  isDeleteModalOpen.value = true
}

function closeDeleteModal() {
  isDeleteModalOpen.value = false
  propertyToDelete.value = null
}

function confirmDelete() {
  if (propertyToDelete.value) {
    router.delete(`/properties/${propertyToDelete.value}`, {
      onSuccess: () => {
        closeDeleteModal()
      },
    })
  }
}
// Share link functions
function goToShare(id) {
  // Import the env variable
  const baseUrl = import.meta.env.VITE_APP_URL || window.location.origin
  shareLink.value = `${baseUrl}/lead-form/${id}`
  isShareModalOpen.value = true
}

function closeShareModal() {
  isShareModalOpen.value = false
  shareLink.value = null
}
async function copyToClipboard() {
  if (shareLink.value) {
    try {
      await navigator.clipboard.writeText(shareLink.value)
      alert("Link copied to clipboard!") // replace with toast if you have one
    } catch (err) {
      console.error("Failed to copy: ", err)
    }
  }
}
</script>

<template>
  <Head title="My Properties" />

  <AuthenticatedLayout>
    <template #header>
        <h2 class="text-xl font-semibold leading-tight text-white">
          Manage My Properties
        </h2>
      </template>

    <!-- Modal for creating property -->
    <Modal :show="isModalOpen" @close="closeModal">
      <div class="p-6">
      <CreatePropertyForm @close="closeModal" />
      <div class="flex justify-end">
        <PrimaryButton @click="closeModal" >Cancel</PrimaryButton>
      </div>
      </div>
    </Modal>

<!-- Modal for deleting property -->
    <Modal :show="isDeleteModalOpen" @close="closeDeleteModal">
  <div class="p-6">
    <h2 class="text-lg font-semibold text-gray-800">Delete Property</h2>
    <p class="mt-2 text-sm text-gray-600">
      Are you sure you want to delete this property? This action cannot be undone.
    </p>
    <div class="flex justify-end mt-6 space-x-4">
      <PrimaryButton class="bg-gray-400 hover:bg-gray-500" @click="closeDeleteModal">
        Cancel
      </PrimaryButton>
      <PrimaryButton class="bg-red-600 hover:bg-red-700" @click="confirmDelete">
        Yes, Delete
      </PrimaryButton>
    </div>
  </div>
</Modal>

  <div class="flex justify-end">
    <PrimaryButton @click="openModal" class="!text-blue-600 bg-white hover:text-blue-800 hover:bg-blue-200 focus:bg-white">
        + Add Property
      </PrimaryButton>
  </div>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div v-if="!props.properties.data.length" class="p-6 py-12 text-center bg-white shadow sm:rounded-lg">
          <h3 class="text-lg font-semibold text-gray-700">No properties added as yet</h3>
          <div class="mt-4">
            <PrimaryButton @click="openModal">
              Add your first property
            </PrimaryButton>
          </div>
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="property in props.properties.data"
            :key="property.id"
            class="p-5 bg-blue-700 border rounded-lg shadow"
          >
              <div>
                <h3 class="text-2xl font-semibold text-blue-200">{{ property.title }}</h3>
                <p class="pt-3 text-sm text-white">
                  {{ property.description }}
                </p>
                <p class="pt-3 text-sm text-blue-300">
                  {{ property.address }}, {{ property.parish }}
                </p>
              </div>
              <div class="flex gap-2 pt-5">
                <PrimaryButton class="!text-blue-700 bg-white hover:bg-white focus:bg-white" @click="goToShare(property.id)">
                  Share Link
                </PrimaryButton>
                <PrimaryButton class="!text-blue-700 bg-white hover:bg-white focus:bg-white" @click="goToView(property.id)">
                  View
                </PrimaryButton>
                <PrimaryButton class="text-white bg-red-700 hover:bg-red-500" @click="goToDelete(property.id)">
                  Delete
                </PrimaryButton>
              </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Share Link Modal -->
<Modal :show="isShareModalOpen" @close="closeShareModal">
  <div class="p-6">
    <h2 class="text-lg font-semibold text-gray-800">Share Property</h2>
    <p class="mt-2 text-sm text-gray-600">
      Copy the link below to share this property:
    </p>
    <div class="flex items-center justify-between mt-4 space-x-2">
      <input
        type="text"
        :value="shareLink"
        readonly
        class="w-full p-2 text-sm border rounded"
      />
      <PrimaryButton class="text-white bg-blue-600 hover:bg-blue-700" @click="copyToClipboard">
        Copy Link
      </PrimaryButton>
    </div>
    <div class="flex justify-end mt-6">
      <PrimaryButton class="bg-gray-400 hover:bg-gray-500" @click="closeShareModal">
        Close
      </PrimaryButton>
    </div>
  </div>
</Modal>
  </AuthenticatedLayout>
</template>
