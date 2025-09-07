<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, router, Head, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import Select from '@/Components/Select.vue'
import Modal from '@/Components/Modal.vue'

const page = usePage()

const props = defineProps({
  questions:   Array,
  property_id: Number,
  property: Object,
  rules: Array,  
  emailTemplates: { type: Array, default: () => [] },
  automationSettings: { type: Array, default: () => [] },
})

// --- Manage Automation (multi-actions) ---
const manageForm = useForm({
  actions: []
})

// base form for creating questions
const form = useForm({
  property_id: props.property_id,
  questions: props.questions.map(q => ({
    id: q.id,
    question: q.question,
    type:     q.type,
    options:  typeof q.options === 'string'
      ? JSON.parse(q.options)
      : (q.options || []),
  })),
})


watch(() => props.automationSettings, (newVal) => {
  if (newVal.length && !manageForm.actions.length) {
    manageForm.actions = newVal.map(a => ({
      type: a.action,
      template_id: a.template_id ?? "",
      agent_email: a.agent_email ?? page.props.auth.user.email,
      send_method: a.send_method ?? "immediate"
    }))
  }
})

// Add an action
function addAction() {
  manageForm.actions.push({
    type: "",
    template_id: "",
    agent_email: page.props.auth.user.email,
    send_method: "immediate"
  })
}

// Remove an action
function removeAction(index) {
  manageForm.actions.splice(index, 1)
}

// Validate Save button
const isSaveDisabled = computed(() => {
  if (manageForm.processing || !manageForm.actions.length) return true
  return manageForm.actions.some(action => {
    switch (action.type) {
      case "send_email":
        return !action.template_id
      case "email_agent":
        return !action.agent_email
      default:
        return true
    }
  })
})

// Save automation settings
function saveAutomation() {
  const cleanedActions = manageForm.actions.map(a => {
    const base = { type: a.type, send_method: a.send_method }
    switch (a.type) {
      case "send_email":
        return { ...base, template_id: a.template_id }
      case "email_agent":
        return { ...base, agent_email: a.agent_email }
      case "schedule_visit":
        return { ...base }
      default:
        return base
    }
  })

  manageForm.transform(() => ({ actions: cleanedActions }))
    .post(route('qualification-automation.store', { property: props.property.id }), {
      onSuccess: () => closeManageModal()
    })
}

// View Property
function goToView(id) {
  router.visit(`/properties/${id}`)
}

// build a Map for quick lookup
const ruleLookup = computed(() =>
  new Map(props.rules.map(r => [r.lead_question_id, r.answer]))
)

// Question management
function addQuestion()      { form.questions.push({ question: '', type: 'input', options: [] }) }
function removeQuestion(i)  { form.questions.splice(i, 1) }
function addOption(i)       { form.questions[i].options.push('') }
function removeOption(i,j)  { form.questions[i].options.splice(j, 1) }
function saveQuestions()    { form.post(route('lead-questions.store')) }

// Manage-automation modal state
const isManageModalOpen = ref(false)

// check if there is at least one email-type question
const hasEmailQuestion = computed(() =>
  form.questions.some(q => q.type === 'email')
)

function openManageModal() {
  // Clear current actions
  manageForm.actions = []

  if (props.automationSettings.length) {
    manageForm.actions = props.automationSettings.map(a => ({
      type: a.action,
      template_id: a.template_id ?? "",
      agent_email: a.agent_email ?? page.props.auth.user.email,
      send_method: a.send_method ?? "immediate",
    }))
  } else {
    // fallback: add a blank action
    addAction()
  }

  isManageModalOpen.value = true
}
function closeManageModal(){ isManageModalOpen.value = false }

// --- Qualification Rules ---
const isRuleListOpen   = ref(false)
function openRuleList() { isRuleListOpen.value = true }
function closeRuleList(){ isRuleListOpen.value = false }

const isRuleDetailOpen  = ref(false)
const currentQuestion   = ref(null)

const ruleForm = useForm({
  property_id:       props.property_id,
  lead_question_id:  null,
  answer:            '',
})

function openRuleDetail(q) {
  currentQuestion.value = q
  ruleForm.reset()
  ruleForm.lead_question_id = q.id
  const existing = ruleLookup.value.get(q.id)
  ruleForm.answer = existing || ''
  isRuleDetailOpen.value = true
}

function closeRuleDetail() {
  isRuleDetailOpen.value = false
  currentQuestion.value = null
}

const ruleQuestions = computed(() =>
  form.questions.filter(q => ['checkbox','radio'].includes(q.type))
)

function saveRule() {
  ruleForm.post(
    route('qualification-rules.store', { property: props.property_id }),
    { onSuccess: () => closeRuleDetail() }
  )
}

function removeRule(leadQuestionId) {
  router.delete(
    route('qualification-rules.destroy', {
      property:          props.property_id,
      lead_question:     leadQuestionId,
    })
  )
}
</script>

<template>
  <Head title="Manage Lead Questions" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-white">Manage Lead Questions</h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="p-6 py-12 bg-white shadow sm:rounded-lg">
          <!-- Header & Back -->
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">
              Property: {{ property.title }}
            </h2>
            <PrimaryButton @click="goToView(property_id)" class="bg-gray-600 hover:bg-gray-700">
              Back 
            </PrimaryButton>
          </div>

          <!-- Instruction & Rules Button -->
          <div class="mb-6">
            <h2 class="mb-4">
              Streamline your lead qualification by setting up clear rules on the specific questions that determine if a prospect qualifies.
            </h2>
            <PrimaryButton @click="openRuleList" class="bg-blue-600 hover:bg-blue-700">
              Rules
            </PrimaryButton>
          </div>
          <div class="mb-6">
            <h2 class="mb-4">
              Manage the steps after a lead qualifies by setting up automations.
            </h2>
            <PrimaryButton @click="openManageModal" class="bg-blue-600 hover:bg-blue-700">
              Manage
            </PrimaryButton>
          </div>

          <!-- Question Form -->
          <div v-for="(q, index) in form.questions" :key="index" class="p-4 m-10 border rounded">
            <div class="flex items-center justify-between">
              <label class="block w-full">
                <span class="text-sm text-gray-600">Question</span>
                <TextInput v-model="q.question" class="w-full p-2 mt-1 border rounded" placeholder="Enter question" />
              </label>
              <button @click="removeQuestion(index)" class="ml-2 text-red-600 hover:underline">Remove</button>
            </div>

            <label class="block mt-5">
              <span class="text-sm text-gray-600">Response Type</span>
              <Select
                v-model="q.type"
                class="p-2 border rounded"
                :options="[
                  { label: 'Input', value: 'input' },
                  { label: 'Email', value: 'email' },
                  { label: 'Textarea', value: 'textarea' },
                  { label: 'File',     value: 'file'     },
                  { label: 'Checkbox', value: 'checkbox' },
                  { label: 'Radio',    value: 'radio'    },
                ]"
              />
            </label>

            <!-- Options for checkbox/radio -->
            <div v-if="q.type==='checkbox'||q.type==='radio'" class="mt-2 space-y-1">
              <label class="text-sm font-medium text-gray-600">Options</label>
              <div v-for="(opt,oIndex) in q.options" :key="oIndex" class="flex gap-2">
                <TextInput v-model="q.options[oIndex]" class="w-full p-2 border rounded" placeholder="Option text" />
                <button @click="removeOption(index,oIndex)" class="text-sm text-red-500">x</button>
              </div>
              <button @click="addOption(index)" class="mt-1 text-sm text-blue-600">+ Add Option</button>
            </div>

            <!-- File type note -->
            <div v-if="q.type==='file'" class="mt-2">
              <label class="text-sm font-medium text-red-600">
                Acceptable formats: .pdf, .docx, .jpeg, .jpg, .png.
              </label>
            </div>
          </div>

          <!-- Actions -->
           <div class="flex justify-end">
            <label class="text-red-700" v-if="!hasEmailQuestion">An email field type needs to be added.</label>
          </div>
          <div class="flex items-center justify-between">
            <PrimaryButton @click="addQuestion" class="bg-green-600 hover:bg-green-700">+ Add Question</PrimaryButton>
            <PrimaryButton @click="saveQuestions" :disabled="!hasEmailQuestion" class="bg-green-600 hover:bg-green-700 disabled:bg-slate-600">Save</PrimaryButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Rule List Modal -->
    <Modal :show="isRuleListOpen" @close="closeRuleList">
      <div class="p-5">
        <h3 class="text-lg font-semibold">Select a Question to Rule</h3>
        <div class="p-4 space-y-2 overflow-y-auto max-h-80">
          <p class="text-gray-700">
            Click “Set Rule” next to the question whose answer determines qualification.
          </p>
          <ul class="divide-y">
            <li
              v-for="q in ruleQuestions"
              :key="q.id"
              class="flex items-center justify-between py-2"
            >
              <div>
                <span class="text-gray-800">{{ q.question }}</span>
                <span v-if="ruleLookup.get(q.id)" class="text-xs font-medium text-green-600">
                  (Rule: {{ ruleLookup.get(q.id) }})
                </span>
              </div>
              <div class="space-x-2">
                <PrimaryButton
                  v-if="!ruleLookup.get(q.id)"
                  @click="openRuleDetail(q)"
                  class="px-2 py-1 text-sm bg-teal-600 hover:bg-teal-700"
                >
                  Set Rule
                </PrimaryButton>
                <PrimaryButton
                  v-else
                  @click="removeRule(q.id)"
                  class="px-2 py-1 text-sm bg-red-600 hover:bg-red-700"
                >
                  Remove
                </PrimaryButton>
              </div>
            </li>
          </ul>
        </div>
        <PrimaryButton @click="closeRuleList">Close</PrimaryButton>
      </div>
    </Modal>

    <!-- Rule Detail Modal -->
    <Modal :show="isRuleDetailOpen" @close="closeRuleDetail">
      <div class="p-5">
        <h3 class="text-lg font-semibold">Define Qualification Answer</h3>
        <div class="p-4 space-y-4">
          <p class="text-gray-700">
            For <strong class="text-gray-800">{{ currentQuestion.question }}</strong>,
            select the one answer that makes a lead qualify:
          </p>
          <input type="hidden" v-model="ruleForm.lead_question_id" />
          <div v-for="opt in currentQuestion.options" :key="opt" class="flex items-center gap-2">
            <input
              type="radio"
              :value="opt"
              v-model="ruleForm.answer"
              class="form-radio"
              :id="`opt-${opt}`"
            />
            <label :for="`opt-${opt}`" class="text-gray-800">{{ opt }}</label>
          </div>
        </div>
        <PrimaryButton class="mr-2 bg-gray-400 hover:bg-gray-500" @click="closeRuleDetail">Cancel</PrimaryButton>
        <PrimaryButton
          class="bg-blue-600 hover:bg-blue-700"
          @click="saveRule"
          :disabled="!ruleForm.lead_question_id || !ruleForm.answer || ruleForm.processing"
        >
          Save Rule
        </PrimaryButton>
      </div>
    </Modal>

    <!-- Manage Automation Modal -->
    <Modal :show="isManageModalOpen" @close="closeManageModal">
      <div class="p-5">
        <h3 class="mb-4 text-lg font-semibold">Configure Qualified-Lead Automation</h3>

        <!-- loop through actions -->
        <div v-for="(action, idx) in manageForm.actions" :key="idx" class="p-4 mb-4 border rounded bg-gray-50">
          <div class="flex items-center justify-between mb-3">
            <h4 class="font-semibold text-md">Action {{ idx + 1 }}</h4>
            <button type="button" class="text-sm text-red-600 hover:underline" @click="removeAction(idx)">Delete</button>
          </div>

          <!-- Action selector -->
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">Action</label>
            <select v-model="action.type" class="w-full p-2 border rounded">
              <option disabled value="">-- select an action --</option>
              <option value="send_email">Send Email</option>
              <option value="email_agent">Email and Notify me</option>
              <option value="schedule_visit">Send Site Visit Email</option>
            </select>
          </div>

          <!-- Supporting fields -->
          <div v-if="action.type === 'send_email'" class="mt-3">
            <label class="block mb-1 text-sm font-medium text-gray-700">Email Template</label>
            <select v-model="action.template_id" class="w-full p-2 border rounded">
              <option disabled value="">-- select a template --</option>
              <option v-for="tmpl in props.emailTemplates" :key="tmpl.id" :value="tmpl.id">
                {{ tmpl.name }}
              </option>
            </select>
          </div>

          <div v-if="action.type === 'email_agent'" class="mt-3">
            <label class="block mb-1 text-sm font-medium text-gray-700">Agent Email Address</label>
            <input type="email" v-model="action.agent_email" class="w-full p-2 border rounded" placeholder="e.g. agent@example.com" />
          </div>

          <!-- Send method -->
          <div class="mt-4">
            <label class="block mb-1 text-sm font-medium text-gray-700">Send Timing</label>
            <div class="flex items-center space-x-4">
              <label class="inline-flex items-center">
                <input type="radio" value="immediate" v-model="action.send_method" class="form-radio" />
                <span class="ml-2">Immediately after submission</span>
              </label>
              <label class="inline-flex items-center">
                <input type="radio" value="manual" v-model="action.send_method" class="form-radio" />
                <span class="ml-2">Manual send</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Add action button -->
        <div class="mb-6">
          <PrimaryButton class="bg-blue-500 hover:bg-blue-600" @click="addAction">+ Add Action</PrimaryButton>
        </div>

        <!-- Footer -->
        <div class="flex justify-end space-x-2">
          <PrimaryButton class="bg-gray-400 hover:bg-gray-500" @click="closeManageModal">Cancel</PrimaryButton>
          <PrimaryButton class="bg-teal-600 hover:bg-teal-700" @click="saveAutomation" :disabled="isSaveDisabled">Save</PrimaryButton>
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>
