<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { logout } from '@/routes'

</script>

<template>
  <Head title="Dashboard" />

  <div class="min-h-screen bg-slate-900 text-white">
    <header class="bg-slate-800 shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
        <h2 class="font-semibold text-xl leading-tight text-purple-400">
          Dashboard
        </h2>
        <Link :href="logout.url()" method="post" as="button" class="text-sm text-slate-300 hover:text-white transition">
          Log Out
        </Link>
      </div>
    </header>

    <main>
      <div class="max-w-7xl mx-auto py-12 sm:px-6 lg:px-8">
        <div class="bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-slate-300">
            You're logged in!
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
