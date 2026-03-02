<template>
  <AppLayout>
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">All Transactions</h2>
      <div class="flex gap-2"> 
        <!-- Export Dropdown -->
        <div class="relative">
          <button
            @click="exportDropdown = !exportDropdown"
            class="bg-green-600 text-white px-4 py-2 rounded text-sm flex items-center gap-2"
          >
            ⬇ Export
          </button>

          <div
            v-if="exportDropdown"
            class="absolute right-0 mt-2 w-40 bg-white rounded-xl shadow-lg border z-50"
          >
            <a
              :href="route('transactions.export', { format: 'csv' })"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="exportDropdown = false"
            >
              📄 Export CSV
            </a>

            <a
              :href="route('transactions.export', { format: 'excel' })"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="exportDropdown = false"
            >
              📊 Export Excel
            </a>

            <a
              :href="route('transactions.export', { format: 'pdf' })"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="exportDropdown = false"
            >
              📑 Export PDF
            </a>
          </div>
        </div>

        <Link href="/transactions/create" class="bg-blue-600 text-white px-4 py-2 rounded text-sm">
          + Add Transaction
        </Link>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl shadow-sm mb-6 grid grid-cols-6 gap-3">
      <input
        v-model="filterForm.search"
        type="text"
        placeholder="Search..."
        class="border rounded px-3 py-2 text-sm"
      />

      <select v-model="filterForm.type" class="border rounded px-3 py-2 text-sm">
        <option value="">All Types</option>
        <option value="income">Income</option>
        <option value="expense">Expense</option>
      </select>

      <input v-model="filterForm.date_from" type="date" class="border rounded px-3 py-2 text-sm" />
      <input v-model="filterForm.date_to" type="date" class="border rounded px-3 py-2 text-sm" />

      <button @click="applyFilters" class="bg-blue-600 text-white rounded px-2 py-2 text-xs font-semibold">
        Filter
      </button>

      <button @click="resetFilters" class="bg-gray-100 text-gray-600 rounded px-2 py-2 text-xs font-semibold hover:bg-gray-200">
        Reset
      </button>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-sm">Date</th>
            <th class="px-4 py-3 text-left text-sm">Title</th>
            <th class="px-4 py-3 text-left text-sm">Type</th>
            <th class="px-4 py-3 text-left text-sm">Category</th>
            <th class="px-4 py-3 text-right text-sm">Amount</th>
            <th class="px-4 py-3 text-center text-sm">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="transaction in transactions.data" :key="transaction.id" class="border-t hover:bg-gray-50">
            <td class="px-4 py-3 text-sm">{{ transaction.transaction_date }}</td>
            <td class="px-4 py-3">
              <div class="font-medium">{{ transaction.title }}</div>
              <div class="text-xs text-gray-400">{{ transaction.description }}</div>
            </td>
            <td class="px-4 py-3">
              <span
                :class="transaction.type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                class="px-2 py-0.5 rounded-full text-xs font-medium capitalize"
              >
                {{ transaction.type }}
              </span>
            </td>
            <td class="px-4 py-3 text-sm">{{ transaction.category }}</td>
            <td
              class="px-4 py-3 text-right font-semibold"
              :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'"
            >
              {{ transaction.type === 'income' ? '+' : '-' }}₱{{ Number(transaction.amount).toLocaleString() }}
            </td>
            <td class="px-4 py-3 text-center">
              <Link :href="`/transactions/${transaction.id}/edit`" class="text-blue-600 hover:underline text-sm mr-3">
                Edit
              </Link>
              <button @click="deleteTransaction(transaction.id)" class="text-red-500 hover:underline text-sm">
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  transactions: {
    type: Object,
    default: () => ({ data: [] })
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const exportDropdown = ref(false)

const filterForm = reactive({
  search: props.filters.search || '',
  type: props.filters.type || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || ''
})

const closeExportDropdown = (e) => {
  if (!e.target.closest('.relative')) {
    exportDropdown.value = false
  }
}

const applyFilters = () => {
  router.get('/transactions', filterForm, { preserveState: true })
}

const deleteTransaction = (id) => {
  if (confirm('Are you sure you want to delete this transaction?')) {
    router.delete(`/transactions/${id}`)
  }
}

const resetFilters = () => {
  filterForm.search = ''
  filterForm.type = ''
  filterForm.date_from = ''
  filterForm.date_to = ''
  router.get('/transactions', {}, { preserveState: true })
}

onMounted(() => document.addEventListener('click', closeExportDropdown))
onUnmounted(() => document.removeEventListener('click', closeExportDropdown))
</script>