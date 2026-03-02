<template>
  <div
    class="flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0"
    :style="{
      backgroundImage: `url(${bg})`,
      backgroundSize: '100%',
      backgroundPosition: 'left',
      backgroundRepeat: 'no-repeat'
    }"
  >

    <!-- Logo -->
    <div class="flex flex-col items-center">
      <Link href="/">
        <img :src="logo" alt="Logo" class="h-10 w-auto object-contain" />
      </Link>

      <!-- Slogan -->
      <h1 class="mt-3 italic text-lg font-semibold text-green-600">
        Track Smart. Spend Wise.
      </h1>
    </div>

    <!-- Card -->
    <div class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg">
      <slot />
    </div>

  </div>
</template>

<script setup>
import logo from '../../images/logo.png'
import bg from '../../images/background.webp'
import { Link } from '@inertiajs/vue3'
</script>