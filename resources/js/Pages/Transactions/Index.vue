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
            Export
          </button>

          <div
            v-if="exportDropdown"
            class="absolute right-0 mt-2 w-40 bg-white rounded-xl shadow-lg border z-50"
          >
            <a
              :href="route('transactions.export', { format: 'csv', ...filterForm })"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="exportDropdown = false"
            >
              📄 Export CSV
            </a>

            <a
              :href="route('transactions.export', { format: 'excel', ...filterForm })"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="exportDropdown = false"
            >
              📊 Export Excel
            </a>

            <a
              :href="route('transactions.export', { format: 'pdf', ...filterForm })"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="exportDropdown = false"
            >
              📑 Export PDF
            </a>
          </div>
        </div>

        <Link
          :href="route('transactions.create')"
          class="bg-blue-600 text-white px-4 py-2 rounded text-sm"
        >
          + Add New Transaction
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
        @input="applyFilters"
      />

      <select
        v-model="filterForm.type"
        class="border rounded px-3 py-2 text-sm"
        @change="onTypeChange"
      >
        <option value="">All Types</option>
        <option value="income">Income</option>
        <option value="expense">Expense</option>
      </select>

      <!-- Category — changes based on selected type -->
      <select
        v-model="filterForm.category"
        class="border rounded px-3 py-2 text-sm"
        @change="applyFilters"
      >
        <option value="">All Categories</option>

        <template v-if="filterForm.type === 'income'">
          <option v-for="cat in income" :key="cat.id" :value="cat.name">
            {{ cat.name }}
          </option>
        </template>

        <template v-else-if="filterForm.type === 'expense'">
          <option v-for="cat in expense" :key="cat.id" :value="cat.name">
            {{ cat.name }}
          </option>
        </template>

        <template v-else>
          <optgroup label="Income">
            <option v-for="cat in income" :key="'i-' + cat.id" :value="cat.name">
              {{ cat.name }}
            </option>
          </optgroup>
          <optgroup label="Expense">
            <option v-for="cat in expense" :key="'e-' + cat.id" :value="cat.name">
              {{ cat.name }}
            </option>
          </optgroup>
        </template>
      </select>

      <input
        v-model="filterForm.date_from"
        type="date"
        class="border rounded px-3 py-2 text-sm"
        @change="applyFilters"
      />

      <input
        v-model="filterForm.date_to"
        type="date"
        class="border rounded px-3 py-2 text-sm"
        @change="applyFilters"
      />

      <button
        @click="resetFilters"
        class="bg-gray-100 text-gray-600 rounded px-2 py-2 text-xs font-semibold hover:bg-gray-200"
      >
        Reset
      </button>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-sm">Date (YYYY-MM-DD)</th>
            <th class="px-4 py-3 text-left text-sm">Title</th>
            <th class="px-4 py-3 text-left text-sm">Type</th>
            <th class="px-4 py-3 text-left text-sm">Category</th>
            <th class="px-4 py-3 text-right text-sm">Amount</th>
            <th class="px-4 py-3 text-center text-sm">Actions</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="transaction in transactions.data"
            :key="transaction.id"
            class="border-t hover:bg-gray-50"
          >
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
              <Link
                :href="route('transactions.edit', transaction.id)"
                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-500 hover:bg-blue-50 hover:text-blue-700 transition-colors mr-1"
                title="Edit"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                </svg>
              </Link>

              <button
                @click="deleteTransaction(transaction.id)"
                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 hover:bg-red-50 hover:text-red-700 transition-colors"
                title="Delete"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16" />
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
 
      <!-- Pagination -->
      <div class="flex justify-between items-center px-4 py-3 border-t text-sm text-gray-600">
        <span>
          Showing {{ transactions.from ?? 0 }}–{{ transactions.to ?? 0 }} of {{ transactions.total }}
        </span>

        <div class="flex gap-2">
          <button
            v-if="transactions.current_page > 1"
            @click="goToPage(transactions.current_page - 1)"
            class="px-3 py-1 rounded border bg-white text-gray-600 hover:bg-gray-50"
          >
            Previous
          </button>

          <span class="px-3 py-1 text-gray-500">
            Page {{ transactions.current_page }} of {{ transactions.last_page }}
          </span>

          <button
            v-if="transactions.current_page < transactions.last_page"
            @click="goToPage(transactions.current_page + 1)"
            class="px-3 py-1 rounded border bg-white text-gray-600 hover:bg-gray-50"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

/**
 * @param {Object} transactions - paginated transaction data from the server
 * @param {Object} filters      - active filter values to pre-populate the filter bar
 * @param {Array}  income       - list of income categories for the category filter
 * @param {Array}  expense      - list of expense categories for the category filter
 */
const props = defineProps({
  transactions: {
    type: Object,
    default: () => ({ data: [] })
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  income: {
    type: Array,
    default: () => []
  },
  expense: {
    type: Array,
    default: () => []
  }
})

const NAVIGATE_OPTIONS = { preserveState: true, preserveScroll: true }

const exportDropdown = ref(false)

const filterForm = reactive({
  search:    props.filters.search    || '',
  type:      props.filters.type      || '',
  category:  props.filters.category  || '',
  date_from: props.filters.date_from || '',
  date_to:   props.filters.date_to   || ''
})

/** Navigate to the transactions index with the given query parameters */
const navigate = (params = {}) => {
  router.get(route('transactions.index'), params, NAVIGATE_OPTIONS)
}

const closeExportDropdown = (e) => {
  if (!e.target.closest('.relative')) {
    exportDropdown.value = false
  }
}

const applyFilters = () => navigate(filterForm)

const onTypeChange = () => {
  filterForm.category = ''
  applyFilters()
}

const goToPage = (page) => navigate({ ...filterForm, page })

const deleteTransaction = (id) => {
  if (confirm('Are you sure you want to delete this transaction?')) {
    router.delete(route('transactions.destroy', id))
  }
}

const resetFilters = () => {
  filterForm.search    = ''
  filterForm.type      = ''
  filterForm.category  = ''
  filterForm.date_from = ''
  filterForm.date_to   = ''
  navigate()
}

onMounted(() => document.addEventListener('click', closeExportDropdown))
onUnmounted(() => document.removeEventListener('click', closeExportDropdown))
</script>