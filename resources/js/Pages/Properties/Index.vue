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
</script>

<template>
  <Head title="My Properties" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          My Properties
        </h2>
        <PrimaryButton @click="openModal">
          + Add Property
        </PrimaryButton>
      </div>
    </template>

    <!-- Modal for creating property -->
    <Modal :show="isModalOpen" @close="closeModal">
      <CreatePropertyForm @close="closeModal" />
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
            class="p-4 bg-white rounded shadow"
          >
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ property.title }}</h3>
                <p class="text-sm text-gray-600">
                  {{ property.address }}, {{ property.parish }}
                </p>
                <p class="text-sm text-gray-600">
                  {{ property.price }} {{ property.currency }} — {{ property.status }}
                </p>
              </div>
              <div>
                <PrimaryButton class="ml-2 text-white bg-blue-700" @click="goToView(property.id)">
                  View
                </PrimaryButton>
                <PrimaryButton class="ml-2 text-white bg-red-700" @click="goToDelete(property.id)">
                  Delete
                </PrimaryButton>
              </div>
   
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
