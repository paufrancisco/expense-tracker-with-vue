<template>
  <AppLayout>
    <h2 class="text-2xl font-bold mb-6">Dashboard — {{ currentMonth }}</h2>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <SummaryCard
        label="Total Income"
        :value="totalIncome"
        subtitle="This month"
        bgColor="bg-green-50"
        valueColor="text-green-700"
        labelColor="text-green-600"
      />
      <SummaryCard
        label="Total Expenses"
        :value="totalExpense"
        subtitle="This month"
        bgColor="bg-red-50"
        valueColor="text-red-700"
        labelColor="text-red-600"
      />
      <SummaryCard
        label="Net Balance"
        :value="balance"
        subtitle="Income minus Expenses"
        bgColor="bg-blue-50"
        valueColor="text-blue-700"
        labelColor="text-blue-600"
      />
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <!-- Pie Chart: Expenses by Category -->
      <div class="bg-white p-6 rounded-xl shadow-sm">
        <h3 class="font-semibold mb-4">Expenses by Category</h3>
        <Pie :data="pieData" :options="{ responsive: true }" />
      </div>

      <!-- Bar Chart: Monthly Overview -->
      <div class="bg-white p-6 rounded-xl shadow-sm">
        <h3 class="font-semibold mb-4">Monthly Overview (Last 6 Months)</h3>
        <Bar :data="barData" :options="{ responsive: true }" />
      </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white p-6 rounded-xl shadow-sm">
      <div class="flex justify-between items-center mb-4">
        <h3 class="font-semibold">Recent Transactions</h3>
        <Link href="/transactions" class="text-blue-600 text-sm">View All →</Link>
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
          <tr v-for="transaction in recentTransactions" :key="transaction.id" class="border-b">
            <td class="py-2">{{ transaction.transaction_date }}</td>
            <td class="py-2">{{ transaction.title }}</td>
            <td class="py-2">{{ transaction.category }}</td>
            <td
              class="py-2 text-right"
              :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'"
            >
              {{ transaction.type === 'income' ? '+' : '-' }}₱{{ transaction.amount }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Pie, Bar } from 'vue-chartjs'
import { Chart, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement } from 'chart.js'
import AppLayout from '@/Layouts/AppLayout.vue'
import SummaryCard from '@/Components/SummaryCard.vue'

// Register Chart.js components
Chart.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement)

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
  monthlyData: {
    type: Array,
    default: () => []
  },
  recentTransactions: {
    type: Array,
    default: () => []
  }
})

const currentMonth = new Date().toLocaleString('default', { month: 'long', year: 'numeric' })

// Build pie chart data from expenseByCategory object
const pieData = computed(() => ({
  labels: Object.keys(props.expenseByCategory),
  datasets: [{
    data: Object.values(props.expenseByCategory),
    backgroundColor: ['#EF4444', '#F97316', '#EAB308', '#22C55E', '#3B82F6', '#8B5CF6', '#EC4899']
  }]
}))

// Build bar chart data from monthlyData array
const barData = computed(() => ({
  labels: props.monthlyData.map(month => month.month),
  datasets: [
    {
      label: 'Income',
      data: props.monthlyData.map(month => month.income),
      backgroundColor: '#22C55E'
    },
    {
      label: 'Expenses',
      data: props.monthlyData.map(month => month.expense),
      backgroundColor: '#EF4444'
    }
  ]
}))
</script>

<style scoped>
</style>