<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
        <h1 class="text-xl font-bold text-blue-700">💰 Finance Tracker</h1>
        <div class="flex items-center gap-4">
          <Link href="/" class="text-gray-600 hover:text-blue-600">Dashboard</Link>
          <Link href="/transactions" class="text-gray-600 hover:text-blue-600">Transactions</Link>
          <Link href="/transactions/create" class="bg-blue-600 text-white px-3 py-1.5 rounded">
            + Add New
          </Link>
          <!-- User dropdown -->
          <span class="text-gray-500 text-sm">{{ $page.props.auth.user.name }}</span>
          <Link href="/logout" method="post" class="text-red-500 text-sm">Logout</Link>
        </div>
      </div>
    </nav>

    <!-- Flash messages -->
    <div v-if="$page.props.flash?.success" class="bg-green-50 border-l-4 border-green-500 p-4 m-4">
      {{ $page.props.flash.success }}
    </div>

    <!-- Page content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<style scoped>
</style>