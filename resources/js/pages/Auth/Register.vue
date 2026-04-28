<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import { register, login } from '@/routes'
import InputLabel from '@/components/InputLabel.vue'
import TextInput from '@/components/TextInput.vue'
import InputError from '@/components/InputError.vue'

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(register.url(), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <Head title="Register" />

  <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-900">
    <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-slate-800 shadow-xl overflow-hidden sm:rounded-2xl border border-slate-700">
      <div class="mb-8 text-center">
        <h2 class="text-3xl font-bold text-white mb-2">Create Account</h2>
        <p class="text-slate-400 text-sm">Join TaskFlow to manage your tasks efficiently</p>
      </div>

      <form @submit.prevent="submit">
        <div>
          <InputLabel for="name" value="Name" />
          <TextInput
            id="name"
            type="text"
            v-model="form.name"
            required
            autofocus
            autocomplete="name"
          />
          <InputError :message="form.errors.name" />
        </div>

        <div class="mt-4">
          <InputLabel for="email" value="Email" />
          <TextInput
            id="email"
            type="email"
            v-model="form.email"
            required
            autocomplete="username"
          />
          <InputError :message="form.errors.email" />
        </div>

        <div class="mt-4">
          <InputLabel for="password" value="Password" />
          <TextInput
            id="password"
            type="password"
            v-model="form.password"
            required
            autocomplete="new-password"
          />
          <InputError :message="form.errors.password" />
        </div>

        <div class="mt-4">
          <InputLabel for="password_confirmation" value="Confirm Password" />
          <TextInput
            id="password_confirmation"
            type="password"
            v-model="form.password_confirmation"
            required
            autocomplete="new-password"
          />
          <InputError :message="form.errors.password_confirmation" />
        </div>

        <div class="flex items-center justify-between mt-8">
          <Link
            :href="login.url()"
            class="text-sm text-purple-400 hover:text-purple-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 focus:ring-offset-slate-900 transition"
          >
            Already registered?
          </Link>

          <button
            class="inline-flex items-center px-6 py-3 bg-purple-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-500 focus:bg-purple-500 active:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition ease-in-out duration-150"
            :class="{ 'opacity-50': form.processing }"
            :disabled="form.processing"
          >
            Register
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
