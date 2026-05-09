<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import { update, password } from '@/actions/App/Http/Controllers/ProfileController'
import { index as dashboard } from '@/actions/App/Http/Controllers/ProjectController'

defineProps<{
    status?: string
}>()

const user = usePage().props.auth.user

const profileForm = useForm({
    name: user.name,
    email: user.email,
    avatar: null as File | null,
})

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const avatarInput = ref<HTMLInputElement | null>(null)

const updateProfile = () => {
    profileForm.post(update.url(), {
        preserveScroll: true,
        onSuccess: () => {
            profileForm.reset('avatar')
            if (avatarInput.value) {
                avatarInput.value.value = ''
            }
        },
    })
}

const updatePassword = () => {
    passwordForm.put(password.url(), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    })
}
</script>

<template>
  <Head title="Profile" />

  <div class="min-h-screen bg-slate-900 text-white flex flex-col">
    <header class="bg-slate-800 shadow sticky top-0 z-10 border-b border-slate-700">
      <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
        <h2 class="font-bold text-2xl leading-tight text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-500">
          Profile Settings
        </h2>
        
        <Link :href="dashboard.url()" class="text-sm text-slate-400 hover:text-white transition whitespace-nowrap">
            Back to Dashboard
        </Link>
      </div>
    </header>

    <main class="flex-1 max-w-3xl w-full mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
      
      <!-- Profile Information -->
      <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-sm p-6 sm:p-8">
        <h3 class="text-lg font-medium text-slate-100 mb-4">Profile Information</h3>
        <p class="text-sm text-slate-400 mb-6">Update your account's profile information and email address.</p>
        
        <form @submit.prevent="updateProfile" class="space-y-6">
            <!-- Avatar Display -->
            <div class="flex items-center space-x-6">
                <div class="h-20 w-20 rounded-full overflow-hidden bg-slate-700 border-2 border-slate-600 flex-shrink-0">
                    <img v-if="user.avatar_url" :src="user.avatar_url" alt="Avatar" class="h-full w-full object-cover" />
                    <svg v-else class="h-full w-full text-slate-500 p-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <label for="avatar" class="block text-sm font-medium text-slate-300">Profile Picture</label>
                    <input 
                        ref="avatarInput"
                        id="avatar" 
                        type="file" 
                        @input="profileForm.avatar = $event.target.files[0]"
                        class="mt-1 block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-purple-600 file:text-white hover:file:bg-purple-500 transition-colors" 
                        accept="image/*"
                    />
                    <div v-if="profileForm.errors.avatar" class="mt-2 text-sm text-red-400">{{ profileForm.errors.avatar }}</div>
                </div>
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-slate-300">Name</label>
                <input 
                    id="name" 
                    v-model="profileForm.name"
                    type="text" 
                    class="mt-1 block w-full rounded-md border-slate-700 bg-slate-900 text-slate-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-3 py-2" 
                />
                <div v-if="profileForm.errors.name" class="mt-2 text-sm text-red-400">{{ profileForm.errors.name }}</div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-300">Email</label>
                <input 
                    id="email" 
                    v-model="profileForm.email"
                    type="email" 
                    class="mt-1 block w-full rounded-md border-slate-700 bg-slate-900 text-slate-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-3 py-2" 
                />
                <div v-if="profileForm.errors.email" class="mt-2 text-sm text-red-400">{{ profileForm.errors.email }}</div>
            </div>

            <div class="flex items-center gap-4">
                <button 
                    type="submit" 
                    :disabled="profileForm.processing"
                    class="inline-flex justify-center rounded-md border border-transparent bg-purple-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-colors disabled:opacity-50"
                >
                    Save Changes
                </button>
                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="profileForm.recentlySuccessful" class="text-sm text-slate-400">Saved.</p>
                </Transition>
            </div>
        </form>
      </div>

      <!-- Update Password -->
      <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-sm p-6 sm:p-8">
        <h3 class="text-lg font-medium text-slate-100 mb-4">Update Password</h3>
        <p class="text-sm text-slate-400 mb-6">Ensure your account is using a long, random password to stay secure.</p>
        
        <form @submit.prevent="updatePassword" class="space-y-6">
            <div>
                <label for="current_password" class="block text-sm font-medium text-slate-300">Current Password</label>
                <input 
                    id="current_password" 
                    v-model="passwordForm.current_password"
                    type="password" 
                    class="mt-1 block w-full rounded-md border-slate-700 bg-slate-900 text-slate-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-3 py-2" 
                />
                <div v-if="passwordForm.errors.current_password" class="mt-2 text-sm text-red-400">{{ passwordForm.errors.current_password }}</div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-300">New Password</label>
                <input 
                    id="password" 
                    v-model="passwordForm.password"
                    type="password" 
                    class="mt-1 block w-full rounded-md border-slate-700 bg-slate-900 text-slate-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-3 py-2" 
                />
                <div v-if="passwordForm.errors.password" class="mt-2 text-sm text-red-400">{{ passwordForm.errors.password }}</div>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-300">Confirm Password</label>
                <input 
                    id="password_confirmation" 
                    v-model="passwordForm.password_confirmation"
                    type="password" 
                    class="mt-1 block w-full rounded-md border-slate-700 bg-slate-900 text-slate-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-3 py-2" 
                />
                <div v-if="passwordForm.errors.password_confirmation" class="mt-2 text-sm text-red-400">{{ passwordForm.errors.password_confirmation }}</div>
            </div>

            <div class="flex items-center gap-4">
                <button 
                    type="submit" 
                    :disabled="passwordForm.processing"
                    class="inline-flex justify-center rounded-md border border-transparent bg-purple-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-colors disabled:opacity-50"
                >
                    Update Password
                </button>
                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="passwordForm.recentlySuccessful" class="text-sm text-slate-400">Saved.</p>
                </Transition>
            </div>
        </form>
      </div>

    </main>
  </div>
</template>
