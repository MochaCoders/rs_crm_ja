<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import Select from '@/Components/Select.vue'
import Modal from '@/Components/Modal.vue'

// Sentinel for 'Add a New Template'
const NEW_TEMPLATE_VALUE = 'new'

const props = defineProps({
  questions:       Array,
  property_id:     Number,
  property:        Object,
  rules:           Array,
  emailTemplates:  { type: Array, default: () => [] },
  actions:         { type: Array, default: () => [] },
  actionOptions:   { type: Array, default: () => [] },
})

// Base form for lead questions
const form = useForm({
  property_id: props.property_id,
  questions: props.questions.map(q => ({
    id: q.id,
    question: q.question,
    type: q.type,
    options: typeof q.options === 'string'
      ? JSON.parse(q.options)
      : (q.options || []),
  })),
})

// Qualification rules
const ruleLookup = computed(() => new Map(props.rules.map(r => [r.lead_question_id, r.answer])))
const ruleQuestions = computed(() => form.questions.filter(q => ['checkbox','radio'].includes(q.type)))
const ruleForm = useForm({ property_id: props.property_id, lead_question_id: null, answer: '' })

// Manage Automation modal
const manageForm = useForm({ action: 'email', template_id: null })
function onTemplateSelect(e) {
  if (e.target.value === NEW_TEMPLATE_VALUE) {
    manageForm.template_id = null
    router.get('/email-templates')
  }
}

// Move trigger options to a constant to handle apostrophes
const actionTriggerOptions = [
  { label: 'Lead Qualifies',      value: 'qualifies'   },
  { label: "Lead Doesn't Qualify", value: 'not_qualify' },
]

// Rules modals
const isRuleListOpen = ref(false)
const isRuleDetailOpen = ref(false)
const currentQuestion = ref(null)
function openRuleList() { isRuleListOpen.value = true }
function closeRuleList() { isRuleListOpen.value = false }
function openRuleDetail(q) {
  currentQuestion.value = q
  ruleForm.reset()
  ruleForm.lead_question_id = q.id
  ruleForm.answer = ruleLookup.value.get(q.id) || ''
  isRuleDetailOpen.value = true
}
function closeRuleDetail() { isRuleDetailOpen.value = false; currentQuestion.value = null }
function saveRule() {
  ruleForm.post(route('qualification-rules.store', { property: props.property_id }), { onSuccess: closeRuleDetail })
}
function removeRule(id) {
  router.delete(route('qualification-rules.destroy', { property: props.property_id, lead_question: id }))
}

// Actions modals
const isActionsListOpen = ref(false)
const isActionCreateOpen = ref(false)
const actionForm = useForm({ trigger: '', action_type: '', template_id: null })
function openActionsList() { isActionsListOpen.value = true }
function closeActionsList() { isActionsListOpen.value = false }
function openActionCreate() { isActionCreateOpen.value = true }
function closeActionCreate() { isActionCreateOpen.value = false }
function saveAction() {
  actionForm.post(route('lead-actions.store', { property: props.property.id }), {
    onSuccess: () => { closeActionCreate(); closeActionsList() }
  })
}

// Question CRUD
function addQuestion()     { form.questions.push({ question: '', type: 'input', options: [] }) }
function removeQuestion(i) { form.questions.splice(i, 1) }
function addOption(i)      { form.questions[i].options.push('') }
function removeOption(i,j) { form.questions[i].options.splice(j, 1) }
function saveQuestions()   { form.post(route('lead-questions.store')) }

// Navigation
function goToView(id) { router.visit(`/properties/${id}`) }
</script>

<template>
  <Head title="Manage Lead Questions" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Manage Lead Questions</h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="p-6 py-12 bg-white shadow sm:rounded-lg">

          <!-- Header & Back -->
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Property: {{ property.title }}</h2>
            <PrimaryButton @click="goToView(property_id)" class="bg-gray-600 hover:bg-gray-700">Back</PrimaryButton>
          </div>

          <!-- Action Buttons -->
          <div class="mb-6 space-x-2">
            <PrimaryButton @click="openRuleList" class="bg-blue-600 hover:bg-blue-700">Rules</PrimaryButton>
            <PrimaryButton @click="openActionsList" class="bg-blue-600 hover:bg-blue-700">Actions</PrimaryButton>
          </div>

          <!-- Question Form -->
          <div v-for="(q, index) in form.questions" :key="index" class="p-4 mb-4 border rounded">
            <div class="flex items-center justify-between">
              <div class="w-full">
                <label class="block text-sm text-gray-600">Question</label>
                <TextInput v-model="q.question" placeholder="Enter question" class="w-full p-2 mt-1 border rounded" />
              </div>
              <button @click="removeQuestion(index)" class="ml-2 text-red-600 hover:underline">Remove</button>
            </div>

            <label class="block mt-4">
              <span class="text-sm text-gray-600">Response Type</span>
              <Select v-model="q.type" class="p-2 mt-1 border rounded" :options="[
                { label: 'Input', value: 'input' },
                { label: 'Textarea', value: 'textarea' },
                { label: 'File', value: 'file' },
                { label: 'Checkbox', value: 'checkbox' },
                { label: 'Radio', value: 'radio' },
              ]" />
            </label>

            <!-- Options -->
            <div v-if="['checkbox','radio'].includes(q.type)" class="mt-2 space-y-2">
              <label class="text-sm font-medium text-gray-600">Options</label>
              <div v-for="(opt, oIndex) in q.options" :key="oIndex" class="flex items-center gap-2">
                <TextInput v-model="q.options[oIndex]" placeholder="Option text" class="flex-1 p-2 border rounded" />
                <button @click="removeOption(index,oIndex)" class="text-red-500">×</button>
              </div>
              <button @click="addOption(index)" class="text-sm text-blue-600">+ Add Option</button>
            </div>

            <!-- File note -->
            <div v-if="q.type === 'file'" class="mt-2">
              <span class="text-sm text-red-600">Acceptable formats: .pdf, .docx, .jpeg, .jpg, .png.</span>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-between">
            <PrimaryButton @click="addQuestion" class="bg-green-600 hover:bg-green-700">+ Add Question</PrimaryButton>
            <PrimaryButton @click="saveQuestions" class="bg-purple-600 hover:bg-purple-700">Save Questions</PrimaryButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Rule List Modal -->
    <Modal :show="isRuleListOpen" @close="closeRuleList">
      <div class="p-5">
        <h3 class="mb-3 text-lg font-semibold">Select a Question to Set Rule</h3>
        <ul class="space-y-2 overflow-auto max-h-60">
          <li v-for="q in ruleQuestions" :key="q.id" class="flex items-center justify-between py-2">
            <div>
              <span class="text-gray-800">{{ q.question }}</span>
              <span v-if="ruleLookup.get(q.id)" class="ml-2 text-xs text-green-600">(Rule: {{ ruleLookup.get(q.id) }})</span>
            </div>
            <div class="space-x-2">
              <PrimaryButton v-if="!ruleLookup.get(q.id)" @click="openRuleDetail(q)" class="px-2 py-1 text-sm bg-teal-600 hover:bg-teal-700">Set Rule</PrimaryButton>
              <PrimaryButton v-else @click="removeRule(q.id)" class="px-2 py-1 text-sm bg-red-600 hover:bg-red-700">Remove Rule</PrimaryButton>
            </div>
          </li>
        </ul>
        <div class="mt-4 text-right">
          <PrimaryButton @click="closeRuleList" class="bg-gray-400 hover:bg-gray-500">Close</PrimaryButton>
        </div>
      </div>
    </Modal>

    <!-- Rule Detail Modal -->
    <Modal :show="isRuleDetailOpen" @close="closeRuleDetail">
      <div class="p-5">
        <h3 class="mb-4 text-lg font-semibold">Define Qualification Answer</h3>
        <p class="mb-4">For <strong>{{ currentQuestion.question }}</strong>, choose the answer that qualifies the lead.</p>
        <div class="space-y-3">
          <div v-for="opt in currentQuestion.options" :key="opt" class="flex items-center">
            <input type="radio" :value="opt" v-model="ruleForm.answer" class="form-radio" :id="`opt-${opt}`" />
            <label :for="`opt-${opt}`" class="ml-2 text-gray-800">{{ opt }}</label>
          </div>
        </div>
        <div class="flex justify-end mt-6 space-x-2">
          <PrimaryButton @click="closeRuleDetail" class="bg-gray-400 hover:bg-gray-500">Cancel</PrimaryButton>
          <PrimaryButton @click="saveRule" :disabled="!ruleForm.answer" class="bg-blue-600 hover:bg-blue-700">Save Rule</PrimaryButton>
        </div>
      </div>
    </Modal>

    <!-- Actions List Modal -->
    <Modal :show="isActionsListOpen" @close="closeActionsList">
      <div class="p-6">
        <h3 class="mb-4 text-lg font-semibold">Lead Form Actions</h3>
        <table class="w-full mb-4 border-collapse">
          <thead>
            <tr class="bg-gray-100">
              <th class="px-4 py-2 text-left">Trigger</th>
              <th class="px-4 py-2 text-left">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="act in props.actions" :key="act.id" class="border-t"><td class="px-4 py-2">{{ act.trigger }}</td><td class="px-4 py-2">{{ act.action_type }}</td></tr>
            <tr v-if="!props.actions.length"><td colspan="2" class="px-4 py-6 text-center text-gray-500">No actions found.</td></tr>
          </tbody>
        </table>
        <PrimaryButton @click="openActionCreate" class="bg-green-600 hover:bg-green-700">+ Add Action</PrimaryButton>
      </div>
    </Modal>

    <!-- Add Action Modal -->
    <Modal :show="isActionCreateOpen" @close="closeActionCreate">
      <div class="p-6">
        <h3 class="mb-4 text-lg font-semibold">Add New Action</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Trigger</label>
            <Select
            v-model="actionForm.trigger"
            :options="actionTriggerOptions"
            class="w-full p-2 mt-1 border rounded"
          />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Action</label>
            <select v-model="actionForm.action_type" class="w-full p-2 border rounded">
              <option disabled value="">-- select an action --</option>
              <option v-for="opt in props.actionOptions" :key="opt.value" :value="opt.value">{{ opt.name }}</option>
            </select>
          </div>
          <div v-if="actionForm.action_type==='email'">
            <label class="block mb-1 text-sm font-medium text-gray-700">Email Template</label>
            <select v-model="manageForm.template_id" @change="onTemplateSelect" class="w-full p-2 border rounded">
              <option disabled value="">-- select a template --</option>
              <option :value="NEW_TEMPLATE_VALUE">Add a New Template</option>
              <option v-for="tmpl in props.emailTemplates" :key="tmpl.id" :value="tmpl.id">{{ tmpl.name }}</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end mt-6 space-x-2">
          <PrimaryButton @click="closeActionCreate" class="bg-gray-400 hover:bg-gray-500">Cancel</PrimaryButton>
          <PrimaryButton @click="saveAction" :disabled="!actionForm.trigger" class="bg-teal-600 hover:bg-teal-700">Save</PrimaryButton>
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>
