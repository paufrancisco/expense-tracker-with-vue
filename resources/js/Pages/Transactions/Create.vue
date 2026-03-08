<template>
  <AppLayout>
    <div class="max-w-2xl mx-auto">
      <h2 class="text-2xl font-bold mb-6">Add New Transaction</h2>

      <div class="bg-white p-6 rounded-xl shadow-sm">
        <!-- Type Selector -->
        <div class="grid grid-cols-2 gap-3 mb-6">
          <button
            @click="selectType('income')"
            :class="form.type === 'income' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700'"
            class="py-3 rounded-lg font-semibold"
          >
            ↑ Income
          </button>

          <button
            @click="selectType('expense')"
            :class="form.type === 'expense' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700'"
            class="py-3 rounded-lg font-semibold"
          >
            ↓ Expense
          </button>
        </div>

        <!-- Form Fields -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Title *</label>
            <input
              v-model="form.title"
              type="text"
              placeholder="e.g. Monthly Salary"
              class="w-full border rounded-lg px-3 py-2"
            />
            <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">
              {{ form.errors.title }}
            </p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Amount *</label>
              <input
                v-model="form.amount"
                type="number"
                min="0"
                step="0.01"
                placeholder="0.00"
                class="w-full border rounded-lg px-3 py-2"
              />
              <p v-if="form.errors.amount" class="text-red-500 text-xs mt-1">
                {{ form.errors.amount }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Date *</label>
              <input
                v-model="form.transaction_date"
                type="date"
                class="w-full border rounded-lg px-3 py-2"
              />
              <p v-if="form.errors.transaction_date" class="text-red-500 text-xs mt-1">
                {{ form.errors.transaction_date }}
              </p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Category *</label>
            <select v-model="form.category" class="w-full border rounded-lg px-3 py-2">
              <option value="">Select Category</option>
              <option v-for="cat in currentCategories" :key="cat.id" :value="cat.name">
                {{ cat.name }}
              </option>
            </select>
            <p v-if="form.errors.category" class="text-red-500 text-xs mt-1">
              {{ form.errors.category }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Description (optional)</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full border rounded-lg px-3 py-2"
            ></textarea>
          </div>
        </div>
 
        <!-- Action Buttons -->
        <div class="flex gap-3 mt-6">
          <Link
            href="/transactions"
            class="flex-1 text-center bg-gray-100 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-200"
          >
            Cancel
          </Link>
          <button
            @click="submit"
            :disabled="form.processing"
            class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : 'Save Transaction' }}
          </button>
        </div> 

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue' 

const props = defineProps({
  income: {
    type: Array,
    default: () => []
  },
  expense: {
    type: Array,
    default: () => []
  }
})

const form = useForm({
  type: 'expense',
  title: '',
  amount: '',
  category: '',
  description: '',
  transaction_date: new Date().toISOString().split('T')[0]
})

const currentCategories = computed(() =>
  form.type === 'income' ? props.income : props.expense
)

const selectType = (type) => {
  form.type = type
  form.category = ''
}

const submit = () => {
  form.post('/transactions')
}
</script>