<script setup>
import { ref } from 'vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import ExternalFormLayout from '@/Layouts/ExternalFormLayout.vue'
const props = defineProps({
  questions: Array,
  property_id:Number
})
const generalFiles = ref({})
const form = useForm({
  responses: {}
})

function handleGeneralFileUpload(event) {
  generalFiles.value = Array.from(event.target.files);
}

function submit() {
  const formData = new FormData();

  // ✅ Add property_id
  formData.append('property_id', props.property_id);

  // Append dynamic question responses
  for (const [questionId, response] of Object.entries(form.responses)) {
    if (Array.isArray(response)) {
      response.forEach((val, index) => {
        formData.append(`responses[${questionId}][${index}]`, val);
      });
    } else {
      formData.append(`responses[${questionId}]`, response);
    }
  }

  // Append general files (independent uploads)
  generalFiles.value.forEach((file, i) => {
    formData.append(`attachments[]`, file);
  });

  router.post(route('lead.submit'), formData, {
    forceFormData: true,
    onSuccess: () => console.log("Form submitted successfully"),
    onError: (e) => console.error(e),
  });
}

</script>

<template>
  <ExternalFormLayout>
    <Head title="Application" />
    <div class="max-w-5xl px-6 py-10 mx-auto rounded-2xl">
      <h1 class="mb-8 text-3xl font-bold text-gray-800">Application</h1>
      <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-8">
        <div
          v-for="question in props.questions"
          :key="question.id"
          class="pb-6 border-b last:border-none last:pb-0"
        >
          <label class="block mb-3 text-lg font-semibold text-gray-700">
            {{ question.question }}
          </label>

          <template v-if="question.type === 'input'">
            <TextInput
              v-model="form.responses[question.id]"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-400"
              placeholder="Type here"
            />
          </template>

          <template v-if="question.type === 'email'">
            <TextInput
              type="email"
              v-model="form.responses[question.id]"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-400"
              placeholder="Enter your email address"
            />
          </template>
          
          <template v-if="question.type === 'textarea'">
            <TextArea
              v-model="form.responses[question.id]"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-400"
              placeholder="Type your response"
              rows="4"
            />
          </template>

          <template v-if="question.type === 'radio'">
            <div class="space-y-2">
              <div
                v-for="option in Array.isArray(question.options) ? question.options : JSON.parse(question.options || '[]')"
                :key="option"
                class="flex items-center space-x-2"
              >
                <input
                  type="radio"
                  :value="option"
                  :name="`q_${question.id}`"
                  v-model="form.responses[question.id]"
                  class="text-blue-600 focus:ring-blue-500"
                />
                <span class="text-gray-700">{{ option }}</span>
              </div>
            </div>
          </template>

          <template v-if="question.type === 'checkbox'">
            <div class="space-y-2">
              <div
                v-for="option in Array.isArray(question.options) ? question.options : JSON.parse(question.options || '[]')"
                :key="option"
                class="flex items-center space-x-2"
              >
                <input
                  type="checkbox"
                  :value="option"
                  :name="`q_${question.id}[]`"
                  @change="(e) => {
                    const selected = form.responses[question.id] || [];
                    if (e.target.checked) {
                      selected.push(option);
                    } else {
                      selected.splice(selected.indexOf(option), 1);
                    }
                    form.responses[question.id] = selected;
                  }"
                  class="text-blue-600 focus:ring-blue-500"
                />
                <span class="text-gray-700">{{ option }}</span>
              </div>
            </div>
          </template>


        </div>
        <div class="py-4 border-b">
          <label class="block mb-2 text-lg font-medium text-gray-700">
            Upload Supporting Documents
          </label>
          <p class="block mb-2 text-sm font-medium text-green-700">Pre Approval Letter and Proof of Income</p>
          <input
            type="file"
            multiple
            @change="handleGeneralFileUpload"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-400"
          />
        </div>

        <div class="text-center">
          <button
            type="submit"
            class="px-6 py-3 font-semibold text-white transition bg-blue-600 rounded-lg shadow hover:bg-blue-700"
          >
            Submit
          </button>
        </div>
      </form>
    </div>
  </ExternalFormLayout>
</template>

