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
/**
 * @param {String} label      - the card's heading label text
 * @param {Number} value      - the monetary value to display
 * @param {String} subtitle   - the descriptive text shown below the value
 * @param {String} bgColor    - Tailwind background color class for the card
 * @param {String} labelColor - Tailwind text color class for the label and subtitle
 * @param {String} valueColor - Tailwind text color class for the value
 */
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

const formatAmount = (amount) =>
  new Intl.NumberFormat('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
</script>