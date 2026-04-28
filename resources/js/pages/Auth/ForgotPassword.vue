<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import InputError from '@/components/InputError.vue'
import InputLabel from '@/components/InputLabel.vue'
import TextInput from '@/components/TextInput.vue'
import { email as passwordEmail } from '@/routes/password'

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(passwordEmail.url());
};
</script>

<template>
    <Head title="Forgot Password" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-900">
        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-slate-800 shadow-xl overflow-hidden sm:rounded-2xl border border-slate-700">
            <div class="mb-4 text-sm text-slate-400">
                Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
            </div>

            <div v-if="status" class="mb-4 font-medium text-sm text-green-400 bg-green-900/30 p-3 rounded-lg border border-green-500/50">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                    />

                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="flex items-center justify-end mt-6">
                    <button
                        class="inline-flex items-center px-6 py-3 bg-purple-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-500 focus:bg-purple-500 active:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition ease-in-out duration-150"
                        :class="{ 'opacity-50': form.processing }"
                        :disabled="form.processing"
                    >
                        Email Password Reset Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
