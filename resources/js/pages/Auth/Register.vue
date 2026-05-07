<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import { register, login } from '@/routes';

const props = defineProps<{
    email?: string;
    inviteToken?: string;
}>();

const form = useForm({
    name: '',
    email: props.email || '',
    password: '',
    password_confirmation: '',
    invite_token: props.inviteToken || '',
});

const submit = () => {
    form.post(register.url(), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />

    <div
        class="flex min-h-screen flex-col items-center bg-slate-900 pt-6 sm:justify-center sm:pt-0"
    >
        <div
            class="mt-6 w-full overflow-hidden border border-slate-700 bg-slate-800 px-6 py-8 shadow-xl sm:max-w-md sm:rounded-2xl"
        >
            <div class="mb-8 text-center">
                <h2 class="mb-2 text-3xl font-bold text-white">
                    Create Account
                </h2>
                <p class="text-sm text-slate-400" v-if="inviteToken">
                    You've been invited to join a project!
                </p>
                <p class="text-sm text-slate-400" v-else>
                    Join TaskFlow to manage your tasks efficiently
                </p>
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
                        :readonly="!!inviteToken"
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
                    <InputLabel
                        for="password_confirmation"
                        value="Confirm Password"
                    />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <!-- Hidden invite token field -->
                <input
                    v-if="inviteToken"
                    type="hidden"
                    name="invite_token"
                    :value="form.invite_token"
                />

                <div class="mt-8 flex items-center justify-between">
                    <Link
                        :href="login.url()"
                        class="rounded-md text-sm text-purple-400 transition hover:text-purple-300 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-slate-900 focus:outline-none"
                    >
                        Already registered?
                    </Link>

                    <button
                        class="inline-flex items-center rounded-lg border border-transparent bg-purple-600 px-6 py-3 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-purple-500 focus:bg-purple-500 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-slate-900 focus:outline-none active:bg-purple-700"
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
