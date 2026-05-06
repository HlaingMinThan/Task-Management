<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
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

    <div class="mx-auto max-w-6xl py-6">
        <h1 class="mb-4 text-2xl font-semibold text-white">Project Members</h1>

        <section class="mb-6 rounded bg-slate-800 p-4">
            <h2 class="mb-2 text-lg font-medium text-white">Invite by email</h2>
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
                Invited users receive an email with a link to accept the invite.
            </p>
        </section>

        <section class="mb-6">
            <h2 class="mb-2 text-lg font-medium text-white">Active Members</h2>
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
                            <div class="text-slate-300">{{ m.role }}</div>
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
            <h2 class="mb-2 text-lg font-medium text-white">Pending Invites</h2>
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
                                {{ new Date(inv.expires_at).toLocaleString() }}
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
</template>

<style scoped>
/* minimal styles; project already uses Tailwind */
</style>
