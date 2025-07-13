<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
  type: String,
  required: true,
});

const props = defineProps({
  options: {
    type: Array,
    required: true,
    // Example: [{ label: 'Available', value: 'available' }]
  },
  placeholder: {
    type: String,
    default: '',
  }
});

const input = ref(null);

onMounted(() => {
  if (input.value?.hasAttribute('autofocus')) {
    input.value.focus();
  }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
  <select
    class="w-full p-2 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
    v-model="model"
    ref="input"
  >
    <option v-if="placeholder" disabled value="">
      {{ placeholder }}
    </option>
    <option
      v-for="option in options"
      :key="option.value"
      :value="option.value"
    >
      {{ option.label }}
    </option>
  </select>
</template>
