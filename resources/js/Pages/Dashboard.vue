<template>
  <AppLayout>
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold">Dashboard {{ currentMonth }}</h2>
      <Link href="/transactions/create" class="bg-blue-600 text-white px-3 py-1.5 rounded">
        + Add New
      </Link>
    </div>

    <!-- Main Two Column Layout -->
    <div class="grid grid-cols-2 gap-4 mb-4">

      <!-- Left: Summary Cards -->
      <div class="flex flex-col gap-4">
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

      <!-- Right: Recent Transactions -->
      <div class="bg-white p-4 rounded-xl shadow-sm overflow-auto" style="max-height: 280px;">
        <div class="flex justify-between items-center mb-3">
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
              <td class="py-1.5">{{ transaction.transaction_date }}</td>
              <td class="py-1.5">{{ transaction.title }}</td>
              <td class="py-1.5">{{ transaction.category }}</td>
              <td
                class="py-1.5 text-right"
                :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'"
              >
                {{ transaction.type === 'income' ? '+' : '-' }}₱{{ transaction.amount }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-2 gap-4">

      <!-- Left: Pie Chart with Tabs -->
      <div class="bg-white p-4 rounded-xl shadow-sm">
        <div class="flex gap-2 mb-3">
          <button
            @click="pieTab = 'expense'"
            :class="pieTab === 'expense' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600'"
            class="px-3 py-1 rounded-lg text-xs font-semibold"
          >
            Expenses by Category
          </button>
          <button
            @click="pieTab = 'income'"
            :class="pieTab === 'income' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-600'"
            class="px-3 py-1 rounded-lg text-xs font-semibold"
          >
            Income by Category
          </button>
        </div>
        <div style="height: 220px;">
          <Pie :data="activePieData" :options="PIE_OPTIONS" />
        </div>
      </div>

      <!-- Right: Bar Chart -->
      <div class="bg-white p-4 rounded-xl shadow-sm">
        <h3 class="font-semibold mb-2">Monthly Overview (Last 6 Months)</h3>
        <div style="height: 220px;">
          <Bar :data="barData" :options="BAR_OPTIONS" />
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Pie, Bar } from 'vue-chartjs'
import { Chart, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement } from 'chart.js'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import AppLayout from '@/Layouts/AppLayout.vue'
import SummaryCard from '@/Components/SummaryCard.vue'

Chart.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, ChartDataLabels)

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
  monthlyData: {
    type: Array,
    default: () => []
  },
  recentTransactions: {
    type: Array,
    default: () => []
  }
})

const pieTab = ref('expense')

const currentMonth = computed(() =>
  new Date().toLocaleString('default', { month: 'long', year: 'numeric' })
)

const CHART_COLORS = ['#EF4444', '#F97316', '#EAB308', '#22C55E', '#3B82F6', '#8B5CF6', '#EC4899']

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

const BAR_OPTIONS = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: { boxWidth: 12, padding: 8 }
    },
    datalabels: {
      display: false
    }
  },
  scales: {
    y: { ticks: { maxTicksLimit: 5 } }
  }
}

const expensePieData = computed(() => ({
  labels: Object.keys(props.expenseByCategory),
  datasets: [{
    data: Object.values(props.expenseByCategory),
    backgroundColor: CHART_COLORS
  }]
}))

const incomePieData = computed(() => ({
  labels: Object.keys(props.incomeByCategory),
  datasets: [{
    data: Object.values(props.incomeByCategory),
    backgroundColor: CHART_COLORS
  }]
}))

const activePieData = computed(() =>
  pieTab.value === 'expense' ? expensePieData.value : incomePieData.value
)

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