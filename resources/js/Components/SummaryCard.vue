<template>
  <div :class="['p-6 rounded-xl shadow-sm', bgColor]">
    <p class="text-sm font-medium" :class="labelColor">{{ label }}</p>
    <p class="text-3xl font-bold mt-1" :class="valueColor">
      ₱{{ formatAmount(value) }}
    </p>
    <p class="text-xs mt-2" :class="labelColor">{{ subtitle }}</p>
  </div>
</template>

<script setup>
const props = defineProps({
  label: {
    type: String,
    default: ''
  },
  value: {
    type: Number,
    default: 0
  },
  subtitle: {
    type: String,
    default: ''
  },
  bgColor: {
    type: String,
    default: 'bg-white'
  },
  labelColor: {
    type: String,
    default: 'text-gray-500'
  },
  valueColor: {
    type: String,
    default: 'text-gray-900'
  }
})

const formatAmount = (amount) => {
  return new Intl.NumberFormat('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
}
</script>

<style scoped>
</style>