<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import InputLabel from '@/components/InputLabel.vue'
import TextInput from '@/components/TextInput.vue'
import { login, register } from '@/routes'
import { request as passwordRequest } from '@/routes/password'

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post(login.url(), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Log in" />

  <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-900">
    <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-slate-800 shadow-xl overflow-hidden sm:rounded-2xl border border-slate-700">
      <div class="mb-8 text-center">
        <h2 class="text-3xl font-bold text-white mb-2">Welcome Back</h2>
        <p class="text-slate-400 text-sm">Sign in to continue to TaskFlow</p>
      </div>

      <div v-if="form.errors.email" class="mb-4 font-medium text-sm text-red-400 bg-red-900/30 p-3 rounded-lg border border-red-500/50">
        {{ form.errors.email }}
      </div>

      <form @submit.prevent="submit">
        <div>
          <InputLabel for="email" value="Email" />
          <TextInput
            id="email"
            type="email"
            v-model="form.email"
            required
            autofocus
            autocomplete="username"
          />
        </div>

        <div class="mt-4">
          <div class="flex items-center justify-between">
            <InputLabel for="password" value="Password" />
            <Link
              :href="passwordRequest.url()"
              class="text-sm text-purple-400 hover:text-purple-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 focus:ring-offset-slate-900 transition"
            >
              Forgot your password?
            </Link>
          </div>
          <TextInput
            id="password"
            type="password"
            v-model="form.password"
            required
            autocomplete="current-password"
          />
        </div>

        <div class="block mt-4">
          <label class="flex items-center cursor-pointer">
            <input
              type="checkbox"
              name="remember"
              v-model="form.remember"
              class="rounded border-slate-700 text-purple-600 shadow-sm focus:ring-purple-500 bg-slate-900"
            />
            <span class="ms-2 text-sm text-slate-400">Remember me</span>
          </label>
        </div>

        <div class="flex items-center justify-between mt-8">
          <Link
            :href="register.url()"
            class="text-sm text-slate-400 hover:text-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 focus:ring-offset-slate-900 transition"
          >
            Create an account
          </Link>

          <button
            class="inline-flex items-center px-6 py-3 bg-purple-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-500 focus:bg-purple-500 active:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition ease-in-out duration-150"
            :class="{ 'opacity-50': form.processing }"
            :disabled="form.processing"
          >
            Log in
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
