<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import { login, register } from '@/routes';
import { request as passwordRequest } from '@/routes/password';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(login.url(), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div
        class="flex min-h-screen flex-col items-center bg-slate-900 pt-6 sm:justify-center sm:pt-0"
    >
        <div
            class="mt-6 w-full overflow-hidden border border-slate-700 bg-slate-800 px-6 py-8 shadow-xl sm:max-w-md sm:rounded-2xl"
        >
            <div class="mb-8 text-center">
                <h2 class="mb-2 text-3xl font-bold text-white">Welcome Back</h2>
                <p class="text-sm text-slate-400">
                    Sign in to continue to TaskFlow
                </p>
            </div>

            <div
                v-if="form.errors.email"
                class="mb-4 rounded-lg border border-red-500/50 bg-red-900/30 p-3 text-sm font-medium text-red-400"
            >
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
                            class="rounded-md text-sm text-purple-400 transition hover:text-purple-300 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-slate-900 focus:outline-none"
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

                <div class="mt-4 block">
                    <label class="flex cursor-pointer items-center">
                        <input
                            type="checkbox"
                            name="remember"
                            v-model="form.remember"
                            class="rounded border-slate-700 bg-slate-900 text-purple-600 shadow-sm focus:ring-purple-500"
                        />
                        <span class="ms-2 text-sm text-slate-400"
                            >Remember me</span
                        >
                    </label>
                </div>

                <div class="mt-8 flex items-center justify-between">
                    <Link
                        :href="register.url()"
                        class="rounded-md text-sm text-slate-400 transition hover:text-slate-300 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-slate-900 focus:outline-none"
                    >
                        Create an account
                    </Link>

                    <button
                        class="inline-flex items-center rounded-lg border border-transparent bg-purple-600 px-6 py-3 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-purple-500 focus:bg-purple-500 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-slate-900 focus:outline-none active:bg-purple-700"
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
