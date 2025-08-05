<script setup>
import { ref, nextTick } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Modal from '@/Components/Modal.vue'

import Quill from 'quill'
import 'quill/dist/quill.snow.css'

// Props: list of templates and available variables to insert
const props = defineProps({
  templates: Array,
  variables: { type: Array, default: () => [] } // e.g. [{ label: 'Email', placeholder: '{{email}}' }, ...]
})

// Form state for create/edit
const form = useForm({
  name:    '',
  subject: '',
  body:    ''
})

// Modal state
const isModalOpen = ref(false)
const isEditing   = ref(false)
const editingId   = ref(null)

// Quill instance and container ref
const editorContainer = ref(null)
let quill = null

// Initialize Quill editor
function initQuill() {
  if (!editorContainer.value) return
  quill = new Quill(editorContainer.value, {
    theme: 'snow',
    modules: {
      toolbar: [
        [{ header: [2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['link']
      ]
    }
  })
}

// Open modal to create new template
function openCreate() {
  isEditing.value = false
  editingId.value = null
  form.reset()
  isModalOpen.value = true
  nextTick(() => {
    if (!quill) initQuill()
    quill.setContents([])
  })
}

// Open modal to edit existing template
function openEdit(tpl) {
  isEditing.value = true
  editingId.value = tpl.id
  form.fill({ name: tpl.name, subject: tpl.subject })
  isModalOpen.value = true
  nextTick(() => {
    if (!quill) initQuill()
    quill.clipboard.dangerouslyPasteHTML(tpl.body)
  })
}

// Save (create or update) template
function save() {
  // Capture editor content
  form.body = quill.root.innerHTML
  const method = isEditing.value ? 'put' : 'post'
  const url = isEditing.value
    ? route('email-templates.update', editingId.value)
    : route('email-templates.store')
  form[method](url, { onSuccess: () => (isModalOpen.value = false) })
}

// Delete a template
function remove(id) {
  if (!confirm('Delete this template?')) return
  form.delete(route('email-templates.destroy', id), { preserveState: true })
}

// Insert a variable placeholder at the current cursor position
function insertVariable(variable) {
  const sel = quill.getSelection(true)
  const index = sel?.index ?? quill.getLength()
  quill.insertText(index, variable.placeholder, 'user')
  quill.setSelection(index + variable.placeholder.length)
}
</script>

<template>
  <Head title="Email Templates" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold">Email Templates</h2>
    </template>

    <!-- Templates list -->
    <div class="py-8">
      <div class="max-w-5xl p-6 mx-auto bg-white rounded shadow">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium">Manage Templates</h3>
          <PrimaryButton @click="openCreate" class="bg-blue-600 hover:bg-blue-700">
            + New Template
          </PrimaryButton>
        </div>

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
              <td class="px-4 py-2">{{ tpl.subject }}</td>
              <td class="px-4 py-2 space-x-2 text-right">
                <PrimaryButton @click="openEdit(tpl)" class="text-sm bg-yellow-500 hover:bg-yellow-600">
                  Edit
                </PrimaryButton>
                <PrimaryButton @click="remove(tpl.id)" class="text-sm bg-red-600 hover:bg-red-700">
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
          <!-- Name input -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full p-2 mt-1 border rounded"
            />
            <span v-if="form.errors.name" class="text-sm text-red-600">
              {{ form.errors.name }}
            </span>
          </div>

          <!-- Subject input -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Subject</label>
            <input
              v-model="form.subject"
              type="text"
              class="w-full p-2 mt-1 border rounded"
            />
            <span v-if="form.errors.subject" class="text-sm text-red-600">
              {{ form.errors.subject }}
            </span>
          </div>

          <!-- Variable insertion buttons -->
          <div v-if="props.variables.length" class="flex flex-wrap gap-2">
            <span class="text-sm font-medium text-gray-700">Insert Variable:</span>
            <button
              v-for="v in props.variables"
              :key="v.placeholder"
              @click.prevent="insertVariable(v)"
              class="px-2 py-1 text-sm text-gray-500 bg-gray-200 rounded hover:bg-gray-300"
            >
              {{ v.label }}
            </button>
          </div>

          <!-- Body editor -->
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">Body</label>
            <div
              ref="editorContainer"
              class="min-h-[200px] bg-white"
            ></div>
            <span v-if="form.errors.body" class="text-sm text-red-600">
              {{ form.errors.body }}
            </span>
          </div>
        </div>

        <!-- Modal actions -->
        <div class="flex justify-end mt-6 space-x-2">
          <PrimaryButton class="bg-gray-400 hover:bg-gray-500" @click="isModalOpen = false">
            Cancel
          </PrimaryButton>
          <PrimaryButton class="bg-green-600 hover:bg-green-700" @click="save">
            {{ isEditing ? 'Update' : 'Create' }}
          </PrimaryButton>
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>
