<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    invite: { email: string; role: string };
    projectName: string;
    inviterName: string;
    token: string;
    userExists: boolean;
    canAccept: boolean;
    statusMessage?: string | null;
}>();

const form = useForm({});

function accept() {
    form.post(`/invites/${props.token}/accept`, {
        onSuccess: () => {
            // In most cases controller handles redirect; nothing special here
        },
    });
}
</script>

<template>
    <Head :title="`Accept invite to ${projectName}`" />

    <div class="mx-auto max-w-2xl py-10">
        <div class="rounded bg-slate-800 p-6">
            <h1 class="text-xl font-semibold text-white">
                You're invited to join {{ projectName }}
            </h1>
            <p class="mt-2 text-slate-400">
                Invited by {{ inviterName }} — role:
                <strong class="text-white">{{ invite.role }}</strong>
            </p>

            <div
                v-if="statusMessage"
                class="mt-4 rounded border border-amber-600 bg-amber-900/30 p-3 text-amber-200"
            >
                {{ statusMessage }}
            </div>

            <div v-if="canAccept" class="mt-6">
                <button
                    @click="accept"
                    class="rounded bg-green-600 px-4 py-2 text-white"
                >
                    Accept Invitation
                </button>
            </div>

            <div v-if="!userExists" class="mt-4 text-slate-400">
                <p>
                    Not registered? You'll be redirected to register with your
                    email pre-filled.
                </p>
            </div>
        </div>
    </div>
</template>
