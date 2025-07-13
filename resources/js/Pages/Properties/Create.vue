<script setup>
import { useForm } from '@inertiajs/vue3'
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import Select from '@/Components/Select.vue';
const emit = defineEmits(['close']);

const form = useForm({
  title: '',
  description: '',
  address: '',
  parish: 'Kingston',
  price: '',
  type: 'apartment',
  currency: 'JMD',
})

function submit() {
  form.post('/properties', {
    onSuccess: () => {
      // emit close event to parent (e.g., modal)
      emit('close')
    }
  })
}
</script>

<template>
  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="mb-5 text-gray-600">Add Property Details</div>
      <form @submit.prevent="submit" class="space-y-4">
      <TextInput v-model="form.title" placeholder="Title" class="w-full p-2 border" />
      <TextArea rows="2" v-model="form.description" placeholder="Description" class="w-full p-2 border"/>
      <TextInput v-model="form.address" placeholder="Address" class="w-full p-2 border" />
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
      <button type="submit" class="px-4 py-2 text-white bg-green-600 rounded">
        Save Property
      </button>
    </form>
    </div>
  </div>
</template>