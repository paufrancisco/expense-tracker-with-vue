<template>
  <Head title="Dashboard" />
  <AppLayout>
    <div class="min-h-screen bg-[#BFC9D1] -m-8 p-8">

      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-black">Dashboard — {{ currentMonth }}</h2>
        <Link :href="route('transactions.create')" class="bg-blue-600 text-white px-3 py-1.5 rounded">
          + Add New Transaction
        </Link>
      </div>

      <!-- Summary Cards Row -->
      <div class="grid grid-cols-3 gap-4 mb-4">
        <SummaryCard
          label="Total Income"
          :value="totalIncome"
          subtitle="This month"
          bgColor="bg-green-100"
          valueColor="text-green-700"
          labelColor="text-green-600"
        />
        <SummaryCard
          label="Total Expenses"
          :value="totalExpense"
          subtitle="This month"
          bgColor="bg-red-100"
          valueColor="text-red-700"
          labelColor="text-red-600"
        />
        <SummaryCard
          label="Revenue"
          :value="balance"
          subtitle="Income minus Expenses"
          bgColor="bg-blue-100"
          valueColor="text-blue-700"
          labelColor="text-blue-600"
        />
      </div>

      <!-- Recent Transactions: Income | Expense side by side -->
      <div class="grid grid-cols-2 gap-4 mb-4">

        <!-- Recent Income -->
        <div class="bg-white p-4 rounded-xl shadow-sm overflow-auto" style="max-height: 240px;">
          <div class="flex justify-between items-center mb-3">
            <h3 class="font-semibold text-green-700">Recent Income</h3>
            <Link :href="route('transactions.index', { type: 'income' })" class="text-green-600 text-sm">
              View All →
            </Link>
          </div>
          <table class="w-full text-sm">
            <thead>
              <tr class="text-gray-500 border-b">
                <th class="text-left pb-2">Date</th>
                <th class="text-left pb-2">Title</th>
                <th class="text-left pb-2">Category</th>
                <th class="text-right pb-2">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="recentIncome.length === 0">
                <td colspan="4" class="py-6 text-center text-gray-400 text-sm">
                  <p class="text-2xl mb-1">📭</p>
                  No recent income
                </td>
              </tr>
              <tr v-for="transaction in recentIncome" :key="transaction.id" class="border-b">
                <td class="py-1.5">{{ transaction.transaction_date }}</td>
                <td class="py-1.5">{{ transaction.title }}</td>
                <td class="py-1.5">{{ transaction.category }}</td>
                <td class="py-1.5 text-right text-green-600">
                  +₱{{ formatAmount(transaction.amount) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Recent Expenses -->
        <div class="bg-white p-4 rounded-xl shadow-sm overflow-auto" style="max-height: 240px;">
          <div class="flex justify-between items-center mb-3">
            <h3 class="font-semibold text-red-700">Recent Expenses</h3>
            <Link :href="route('transactions.index', { type: 'expense' })" class="text-red-600 text-sm">
              View All →
            </Link>
          </div>
          <table class="w-full text-sm">
            <thead>
              <tr class="text-gray-500 border-b">
                <th class="text-left pb-2">Date</th>
                <th class="text-left pb-2">Title</th>
                <th class="text-left pb-2">Category</th>
                <th class="text-right pb-2">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="recentExpense.length === 0">
                <td colspan="4" class="py-6 text-center text-gray-400 text-sm">
                  <p class="text-2xl mb-1">📭</p>
                  No recent expenses
                </td>
              </tr>
              <tr v-for="transaction in recentExpense" :key="transaction.id" class="border-b">
                <td class="py-1.5">{{ transaction.transaction_date }}</td>
                <td class="py-1.5">{{ transaction.title }}</td>
                <td class="py-1.5">{{ transaction.category }}</td>
                <td class="py-1.5 text-right text-red-600">
                  -₱{{ formatAmount(transaction.amount) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>

      <!-- Charts Row: Income | Expense Pie Charts -->
      <div class="grid grid-cols-2 gap-4">

        <div class="bg-white p-4 rounded-xl shadow-sm">
          <h3 class="font-semibold text-green-700 mb-3">Income by Category</h3>
          <div style="height: 220px;" class="flex items-center justify-center">
            <Pie v-if="incomeHasData" :data="incomePieData" :options="PIE_OPTIONS" />
            <div v-else class="text-center text-gray-400">
              <p class="text-3xl mb-2">📭</p>
              <p class="text-sm font-medium">No data available</p>
              <p class="text-xs mt-1">Add income transactions this month</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm">
          <h3 class="font-semibold text-red-700 mb-3">Expenses by Category</h3>
          <div style="height: 220px;" class="flex items-center justify-center">
            <Pie v-if="expenseHasData" :data="expensePieData" :options="PIE_OPTIONS" />
            <div v-else class="text-center text-gray-400">
              <p class="text-3xl mb-2">📭</p>
              <p class="text-sm font-medium">No data available</p>
              <p class="text-xs mt-1">Add expense transactions this month</p>
            </div>
          </div>
        </div>

      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import { Pie } from 'vue-chartjs'
import { Chart, ArcElement, Tooltip, Legend } from 'chart.js'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import AppLayout from '@/Layouts/AppLayout.vue'
import SummaryCard from '@/Components/SummaryCard.vue'

Chart.register(ArcElement, Tooltip, Legend, ChartDataLabels)

/**
 * Props received from DashboardController@index via Inertia::render()
 * All values are computed by DashboardService::getDashboardData()
 * and are scoped to the current month only.
 *
 * @prop {Number} totalIncome        - total sum of all income transactions for the current month
 * @prop {Number} totalExpense       - total sum of all expense transactions for the current month
 * @prop {Number} balance            - net balance: totalIncome - totalExpense
 * @prop {Object} incomeByCategory   - income amounts grouped by category name
 *                                     used to build the Income Pie Chart
 *                                     Example: { Salary: 25000, Freelance: 5000 }
 * @prop {Object} expenseByCategory  - expense amounts grouped by category name
 *                                     used to build the Expense Pie Chart
 *                                     Example: { Food: 3000, Rent: 12000 }
 * @prop {Array}  recentTransactions - the 10 most recent transactions ordered by date descending
 *                                     Shape: [{ id, title, category, type, amount, transaction_date }]
 */
const props = defineProps({
  totalIncome: {
    type: Number,
    default: 0
  },
  totalExpense: {
    type: Number,
    default: 0
  },
  balance: {
    type: Number,
    default: 0
  },
  expenseByCategory: {
    type: Object,
    default: () => ({})
  },
  incomeByCategory: {
    type: Object,
    default: () => ({})
  },
  recentTransactions: {
    type: Array,
    default: () => []
  }
})

const CHART_COLORS = ['#22C55E', '#3B82F6', '#EAB308', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316']
const EXPENSE_COLORS = ['#EF4444', '#F97316', '#EAB308', '#8B5CF6', '#EC4899', '#3B82F6', '#14B8A6']

const PIE_OPTIONS = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: { boxWidth: 12, padding: 8 }
    },
    datalabels: {
      color: '#fff',
      font: { weight: 'bold', size: 11 },
      formatter: (value, context) => {
        const total = context.dataset.data.reduce((sum, val) => sum + val, 0)
        const percentage = ((value / total) * 100).toFixed(1)
        return `${percentage}%`
      }
    }
  }
}

const currentMonth = computed(() =>
  new Date().toLocaleString('default', { month: 'long', year: 'numeric' })
)

const recentIncome = computed(() =>
  props.recentTransactions.filter(t => t.type === 'income')
)

const recentExpense = computed(() =>
  props.recentTransactions.filter(t => t.type === 'expense')
)

const buildPieData = (categoryData, colors) => ({
  labels: Object.keys(categoryData),
  datasets: [{ data: Object.values(categoryData), backgroundColor: colors }]
})

const incomePieData = computed(() => buildPieData(props.incomeByCategory, CHART_COLORS))
const expensePieData = computed(() => buildPieData(props.expenseByCategory, EXPENSE_COLORS))

const incomeHasData = computed(() => Object.keys(props.incomeByCategory).length > 0)
const expenseHasData = computed(() => Object.keys(props.expenseByCategory).length > 0)

const formatAmount = (amount) =>
  new Intl.NumberFormat('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount)
</script>