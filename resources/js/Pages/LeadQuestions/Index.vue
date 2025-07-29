<script setup>
import { ref, computed } from 'vue'
import { useForm, router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import Select from '@/Components/Select.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  questions:   Array,
  property_id: Number,
  property: Object,
  rules: Array,  
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

// --- Qualification Rules Modals & State ---

// list modal: choose a question
const isRuleListOpen   = ref(false)
function openRuleList() { isRuleListOpen.value = true }
function closeRuleList(){ isRuleListOpen.value = false }

// detail modal: pick answer for one question
const isRuleDetailOpen  = ref(false)
const currentQuestion   = ref(null)

// useForm for rule persistence
const ruleForm = useForm({
  property_id:       props.property_id,
  lead_question_id:  null,
  answer:            '',
})

function openRuleDetail(q) {
  currentQuestion.value = q

  // Reset and preload ruleForm
  ruleForm.reset()
  ruleForm.lead_question_id = q.id

  // If an existing rule exists, prefill answer
  const existing = ruleLookup.value.get(q.id)
  ruleForm.answer = existing || ''

  isRuleDetailOpen.value = true
}

function closeRuleDetail() {
  isRuleDetailOpen.value = false
  currentQuestion.value = null
}

// only checkbox & radio questions
const ruleQuestions = computed(() =>
  form.questions.filter(q => ['checkbox','radio'].includes(q.type))
)

// update saveRule to post directly from ruleForm
function saveRule() {
  console.log('here');
  ruleForm.post(
    route('qualification-rules.store', { property: props.property_id }),
    {
      onSuccess: () => {
        closeRuleDetail()
      },
    }
  )
}

function removeRule(leadQuestionId) {
  router.delete(
    route('qualification-rules.destroy', {
      property:          props.property_id,
      lead_question:     leadQuestionId,
    }),
    {
      onSuccess: () => {
        // optionally close or refresh modal
      }
    }
  )
}
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
                  { label: 'Input',    value: 'input'    },
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
          <div class="flex items-center justify-between">
            <PrimaryButton @click="addQuestion" class="bg-green-600 hover:bg-green-700">+ Add Question</PrimaryButton>
            <PrimaryButton @click="saveQuestions" class="bg-purple-600 hover:bg-purple-700">Save</PrimaryButton>
          </div>
        </div>
      </div>
    </div>

    <!-- 1) Rule List Modal -->
    <Modal :show="isRuleListOpen" @close="closeRuleList" >
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
                  <span
                    v-if="ruleLookup.get(q.id)"
                    class="text-xs font-medium text-green-600"
                  >
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

    <!-- 2) Rule Detail Modal -->
  <Modal :show="isRuleDetailOpen" @close="closeRuleDetail">
    <div class="p-5">
      <h3 class="text-lg font-semibold">Define Qualification Answer</h3>
      <div class="p-4 space-y-4">
        <p class="text-gray-700">
          For <strong class="text-gray-800">{{ currentQuestion.question }}</strong>,
          select the one answer that makes a lead qualify:
        </p>

        <!-- Hidden field is already set in ruleForm.lead_question_id -->
        <input type="hidden" v-model="ruleForm.lead_question_id" />

        <div
          v-for="opt in currentQuestion.options"
          :key="opt"
          class="flex items-center gap-2"
        >
          <input
            type="radio"
            :value="opt"
            v-model="ruleForm.answer"
            class="form-radio"
            id="`opt-${opt}`"
          />
          <label :for="`opt-${opt}`" class="text-gray-800">{{ opt }}</label>
        </div>
      </div>
      <PrimaryButton
        class="mr-2 bg-gray-400 hover:bg-gray-500"
        @click="closeRuleDetail"
      >
        Cancel
      </PrimaryButton>
      <PrimaryButton
        class="bg-blue-600 hover:bg-blue-700"
        @click="saveRule"
        :disabled="!ruleForm.lead_question_id || !ruleForm.answer || ruleForm.processing"
      >
        Save Rule
      </PrimaryButton>
    </div>
  </Modal>
  </AuthenticatedLayout>
</template>
