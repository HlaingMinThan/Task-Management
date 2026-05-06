<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    project: { id: number; name: string };
    members: Array<any>;
    pendingInvites: Array<any>;
}>();

const form = useForm({ email: '', role: 'member' });
const processing = ref(false);

function submitInvite() {
    processing.value = true;
    form.post(`/projects/${props.project.id}/members`, {
        onSuccess: () => {
            form.reset();
            processing.value = false;
            router.reload();
        },
        onError: () => {
            processing.value = false;
        },
    });
}

function removeMember(memberId: number) {
    if (!confirm('Remove this member from project?')) return;
    router.delete(`/projects/${props.project.id}/members/${memberId}`, {
        onSuccess: () => router.reload(),
    });
}

function cancelInvite(inviteId: number) {
    if (!confirm('Cancel this pending invitation?')) return;
    router.delete(`/projects/${props.project.id}/invites/${inviteId}`, {
        onSuccess: () => router.reload(),
    });
}
</script>

<template>
    <Head :title="`${project.name} — Members`" />

    <div class="flex h-screen flex-col overflow-hidden bg-slate-900">
        <header
            class="shrink-0 border-b border-slate-700 bg-slate-800 shadow-sm"
        >
            <div
                class="mx-auto flex h-16 max-w-full items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <div class="flex items-center space-x-4">
                    <Link
                        href="/dashboard"
                        class="text-slate-400 transition hover:text-white"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </Link>
                    <div>
                        <h2 class="text-xl leading-tight font-bold text-white">
                            {{ project.name }}
                        </h2>
                    </div>
                    <div>
                        <Link
                            :href="`/projects/${project.id}/members`"
                            class="ml-4 inline-flex items-center rounded bg-slate-700 px-3 py-1.5 text-sm text-white hover:bg-slate-600"
                            >Members</Link
                        >
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto">
            <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
                <h1 class="mb-4 text-2xl font-semibold text-white">
                    Project Members
                </h1>

                <section class="mb-6 rounded bg-slate-800 p-4">
                    <h2 class="mb-2 text-lg font-medium text-white">
                        Invite by email
                    </h2>
                    <div class="flex items-center space-x-2">
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="user@example.com"
                            class="flex-1 rounded border border-slate-700 bg-slate-900 p-2 text-white"
                        />
                        <select
                            v-model="form.role"
                            class="rounded border border-slate-700 bg-slate-900 p-2 text-white"
                        >
                            <option value="owner">Owner</option>
                            <option value="editor">Editor</option>
                            <option value="viewer">Viewer</option>
                        </select>
                        <button
                            @click.prevent="submitInvite"
                            :disabled="form.processing || !form.email"
                            class="rounded bg-purple-600 px-3 py-2 text-white"
                        >
                            Send
                        </button>
                    </div>
                    <p class="mt-2 text-sm text-slate-400">
                        Invited users receive an email with a link to accept the
                        invite.
                    </p>
                </section>

                <section class="mb-6">
                    <h2 class="mb-2 text-lg font-medium text-white">
                        Active Members
                    </h2>
                    <div class="rounded bg-slate-800 p-4">
                        <ul>
                            <li
                                v-for="m in members"
                                :key="m.id"
                                class="flex items-center justify-between border-b border-slate-700 p-2"
                            >
                                <div>
                                    <div class="font-medium text-white">
                                        {{ m.user?.name ?? '—' }}
                                    </div>
                                    <div class="text-sm text-slate-400">
                                        {{ m.user?.email ?? '—' }}
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="text-slate-300">
                                        {{ m.role }}
                                    </div>
                                    <button
                                        @click="removeMember(m.id)"
                                        class="text-red-400 hover:underline"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </section>

                <section>
                    <h2 class="mb-2 text-lg font-medium text-white">
                        Pending Invites
                    </h2>
                    <div class="rounded bg-slate-800 p-4">
                        <ul>
                            <li
                                v-for="inv in pendingInvites"
                                :key="inv.id"
                                class="flex items-center justify-between border-b border-slate-700 p-2"
                            >
                                <div>
                                    <div class="font-medium text-white">
                                        {{ inv.email }}
                                    </div>
                                    <div class="text-sm text-slate-400">
                                        {{ inv.role }} — invited by
                                        {{ inv.invitedBy?.name ?? '—' }}
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="text-slate-300">
                                        expires
                                        {{
                                            new Date(
                                                inv.expires_at,
                                            ).toLocaleString()
                                        }}
                                    </div>
                                    <button
                                        @click="cancelInvite(inv.id)"
                                        class="text-yellow-400 hover:underline"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </section>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* minimal styles; project already uses Tailwind */
</style>
