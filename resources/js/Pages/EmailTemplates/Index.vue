<script setup>
import { ref, nextTick, watch } from 'vue'
import { useForm, Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Modal from '@/Components/Modal.vue'

import Quill from 'quill'
import 'quill/dist/quill.snow.css'

/**
 * Expected backend props:
 *  - templates: Array<{ id, name, subject, body }>
 *  - variables: Array<{ label: string, token: string }>
 *  - properties: Array<{ id, title }>
 *  - propertyId: number|string|null
 */
const props = defineProps({
  templates:     { type: Array,  default: () => [] },
  variables:     { type: Array,  default: () => [] },
  properties:    { type: Array,  default: () => [] },
  propertyId:    { type: [Number, String, null], default: null },
})

// Local state for selected property (initialized from props)
const selectedPropertyId = ref(props.propertyId ?? '')

const form = useForm({
  name:    '',
  subject: '',
  body:    '',
})

const isModalOpen = ref(false)
const isEditing   = ref(false)
const editingId   = ref(null)

// Quill editor refs
const editorContainer = ref(null)
let quill = null

/**
 * Always (re)initialize Quill on the current editorContainer.
 * This avoids stale instances after the modal unmounts its content.
 */
function initQuill () {
  if (!editorContainer.value) return

  // Clear container to ensure a clean mount surface
  editorContainer.value.innerHTML = ''

  // Create a fresh instance bound to the current DOM node
  quill = new Quill(editorContainer.value, {
    theme: 'snow',
    modules: {
      toolbar: [
        [{ header: [2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['link'],
      ],
    },
  })
}

/** Insert a token like {{ lead.email }} into Quill body at the current cursor position */
function insertTokenIntoBody(token) {
  if (!quill) return
  const toInsert = `{{ ${token} }}`
  const range = quill.getSelection(true)
  const index = range ? range.index : quill.getLength()
  quill.insertText(index, toInsert)
  quill.setSelection(index + toInsert.length, 0)
}

/** Start create flow */
function openCreate() {
  isEditing.value = false
  editingId.value = null
  form.reset()
  isModalOpen.value = true

  nextTick(() => {
    initQuill()                 // <- always reinit
    quill.clipboard.dangerouslyPasteHTML('') // empty body
  })
}

/** Start edit flow */
function openEdit(tpl) {
  isEditing.value = true
  editingId.value = tpl.id

  // CLEAR then ASSIGN fields
  form.reset()
  form.name    = tpl.name ?? ''
  form.subject = tpl.subject ?? ''
  form.body    = tpl.body ?? ''

  isModalOpen.value = true

  nextTick(() => {
    initQuill()                                 // <- always reinit
    quill.clipboard.dangerouslyPasteHTML(form.body || '')
  })
}

/** Persist template (create or update) */
function save() {
  form.body = quill ? quill.root.innerHTML : form.body

  const method = isEditing.value ? 'put' : 'post'
  // Keep current selected property_id in the URL as query so the page remains scoped
  const query = selectedPropertyId.value ? { property_id: selectedPropertyId.value } : {}

  // Build Ziggy routes using named resource param
  const url = isEditing.value
    ? route('email-templates.update', { email_template: editingId.value, ...query })
    : route('email-templates.store', { ...query })

  form[method](url, {
    onSuccess: () => (isModalOpen.value = false),
  })
}

/** Delete template */
function remove(id) {
  if (!confirm('Delete this template?')) return

  const query = selectedPropertyId.value ? { property_id: selectedPropertyId.value } : {}
  const url = route('email-templates.destroy', { email_template: id, ...query })

  form.delete(url, { preserveState: true })
}

/** When user chooses a property, reload the page data with that property_id */
function onChangeProperty() {
  const query = selectedPropertyId.value ? { property_id: selectedPropertyId.value } : {}
  router.get(route('email-templates.index', query), {}, { preserveState: true, replace: true })
}

/** Clean up old Quill instance when modal closes so we always reinit next open */
watch(isModalOpen, (open) => {
  if (!open) {
    quill = null
  }
})
</script>

<template>
  <Head title="Email Templates" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between w-full">
        <h2 class="text-xl font-semibold text-white">Email Templates</h2>

        <div class="flex items-center gap-2">
          <label for="prop-select" class="text-sm text-white">Select a Property:</label>
          <select
            id="prop-select"
            v-model="selectedPropertyId"
            @change="onChangeProperty"
            class="p-2 text-sm border rounded min-w-[16rem]"
          >
            <option value="">All Properties</option>
            <option
              v-for="p in props.properties"
              :key="p.id"
              :value="p.id"
            >
              {{ p.title }}
            </option>
          </select>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-5xl p-6 mx-auto bg-white rounded shadow">
        <div class="flex items-center justify-between mb-4">
          <h3 v-if="!selectedPropertyId" class="text-lg font-medium">Manage Templates</h3>
          <h3 v-if="selectedPropertyId" class="text-lg font-medium">Manage Templates for {{ props.properties[0].title }}</h3>

          <!-- Only show '+ New Template' when a property is selected -->
          <PrimaryButton
            v-if="selectedPropertyId"
            @click="openCreate"
            class="bg-blue-600 hover:bg-blue-700"
          >
            + New Template
          </PrimaryButton>
        </div>

        <!-- Helper text depending on selection -->
        <p v-if="selectedPropertyId" class="mb-4 text-sm text-gray-600">
          Variables reflect lead questions for the selected property.
        </p>
        <p v-else class="px-3 py-2 mb-4 text-sm border rounded text-amber-700 bg-amber-50 border-amber-200">
          Select a property to create a new email template and load property-specific variables.
        </p>

        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-gray-100">
              <th class="px-4 py-2 text-left">Name</th>
              <th class="px-4 py-2 text-left">Subject</th>
              <th class="px-4 py-2"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="tpl in props.templates" :key="tpl.id" class="border-t">
              <td class="px-4 py-2">{{ tpl.name }}</td>
              <td class="px-4 py-2 truncate max-w-[24rem]" :title="tpl.subject">
                {{ tpl.subject }}
              </td>
              <td class="px-4 py-2 space-x-2 text-right">
                <PrimaryButton
                  @click="openEdit(tpl)"
                  class="text-sm bg-yellow-500 hover:bg-yellow-600"
                >
                  Edit
                </PrimaryButton>
                <PrimaryButton
                  @click="remove(tpl.id)"
                  class="text-sm bg-red-600 hover:bg-red-700"
                >
                  Delete
                </PrimaryButton>
              </td>
            </tr>
            <tr v-if="!props.templates.length">
              <td colspan="3" class="px-4 py-6 text-center text-gray-500">
                No templates found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="isModalOpen" @close="isModalOpen = false">
      <div class="w-full max-w-2xl p-6 bg-white rounded-lg">
        <h3 class="mb-4 text-lg font-semibold">
          {{ isEditing ? 'Edit' : 'New' }} Email Template
        </h3>

        <div class="space-y-4">
          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full p-2 mt-1 border rounded"
              placeholder="Internal name, e.g. Welcome Email"
            />
            <span v-if="form.errors.name" class="text-sm text-red-600">
              {{ form.errors.name }}
            </span>
          </div>

          <!-- Subject -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Subject</label>
            <input
              id="tpl-subject-input"
              v-model="form.subject"
              type="text"
              class="w-full p-2 mt-1 border rounded"
              placeholder="Subject line"
            />
            <span v-if="form.errors.subject" class="text-sm text-red-600">
              {{ form.errors.subject }}
            </span>
          </div>

          <!-- Body toolbar: variable chips -->
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">Body</label>

            <!-- variable chips (built from base + property lead questions) -->
            <div class="flex flex-wrap gap-2 mb-2">
              <button
                v-for="v in props.variables"
                :key="v.token"
                type="button"
                class="px-2 py-1 text-xs bg-gray-100 border rounded hover:bg-gray-200"
                @click="insertTokenIntoBody(v.token)"
                :title="`Insert ${v.label}`"
              >
                {{ v.label }}
              </button>
            </div>

            <!-- Quill editor -->
            <div
              ref="editorContainer"
              id="quill-editor"
              class="min-h-[220px] bg-white"
            ></div>
            <span v-if="form.errors.body" class="text-sm text-red-600">
              {{ form.errors.body }}
            </span>
          </div>
        </div>

        <div class="flex justify-end mt-6 space-x-2">
          <PrimaryButton
            class="bg-gray-400 hover:bg-gray-500"
            @click="isModalOpen = false"
          >
            Cancel
          </PrimaryButton>
          <PrimaryButton
            class="bg-green-600 hover:bg-green-700"
            @click="save"
            :disabled="form.processing"
          >
            {{ isEditing ? 'Update' : 'Create' }}
          </PrimaryButton>
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>
