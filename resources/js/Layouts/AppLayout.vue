<template>
  <div class="min-h-screen bg-[#BFC9D1]">
    <!-- Top Navigation Bar -->
    <nav class="bg-[#25343F] shadow-sm border-b border-gray-700">
      <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-12">
        <Link href="/dashboard">
          <img :src="logo" alt="Track Wise" class="h-5 w-auto object-contain" />
        </Link>

        <div class="flex items-center gap-6">
          <Link
            href="/dashboard"
            :class="page.url === '/dashboard'
              ? 'text-white border-b-2 border-white pb-1'
              : 'text-gray-400 hover:text-white pb-1'"
          >
            Dashboard
          </Link>

          <Link
            href="/transactions"
            :class="isActive('/transactions')
              ? 'text-white border-b-2 border-white pb-1'
              : 'text-gray-400 hover:text-white pb-1'"
          >
            Transactions
          </Link>

          <!-- User Dropdown -->
          <div class="relative">
            <button
              @click="dropdownOpen = !dropdownOpen"
              class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-semibold text-sm focus:outline-none"
            >
              {{ authUser.name.charAt(0).toUpperCase() }}
            </button>

            <!-- Dropdown Menu -->
            <div
              v-if="dropdownOpen"
              class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border z-50"
            >
              <div class="px-4 py-3 border-b">
                <p class="text-sm font-semibold text-gray-800">{{ authUser.name }}</p>
                <p class="text-xs text-gray-400">{{ authUser.email }}</p>
              </div>

              <div class="py-1">
                <Link
                  href="/logout"
                  method="post"
                  class="block w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50"
                  @click="dropdownOpen = false"
                >
                  Logout
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Flash Messages -->
    <div v-if="$page.props.flash?.success" class="bg-green-50 border-l-4 border-green-500 p-4 m-4">
      {{ $page.props.flash.success }}
    </div>

    <!-- Page Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import logo from '../../../resources/images/logo.png'

const page = usePage()
const dropdownOpen = ref(false)

const authUser = computed(() => page.props.auth.user)

const isActive = (path) => page.url === path || page.url.startsWith(path + '/')

const closeDropdown = (e) => {
  if (!e.target.closest('.relative')) {
    dropdownOpen.value = false
  }
}

onMounted(() => document.addEventListener('click', closeDropdown))
onUnmounted(() => document.removeEventListener('click', closeDropdown))
</script>