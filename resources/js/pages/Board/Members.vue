<script setup lang="ts">
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    project: { id: number; name: string };
    members: Array<any>;
    pendingInvites: Array<any>;
}>();

const form = useForm({ email: '', role: '' });
const processing = ref(false);
const page = usePage();
const successMessage = ref<string | null>(null);
const memberRoles = ref<{ [key: number]: string }>({});
const updatingMemberId = ref<number | null>(null);
const removeMemberError = ref<string | null>(null);
const cancelInviteError = ref<string | null>(null);

// Initialize memberRoles from members
props.members.forEach((m) => {
    memberRoles.value[m.id] = m.role;
});

// Watch for errors from backend
watch(
    () => page.props.errors,
    (errors: any) => {
        if (errors && errors.error) {
            if (errors.error.includes('remove')) {
                removeMemberError.value = errors.error;
                setTimeout(() => {
                    removeMemberError.value = null;
                }, 5000);
            } else if (errors.error.includes('cancel')) {
                cancelInviteError.value = errors.error;
                setTimeout(() => {
                    cancelInviteError.value = null;
                }, 5000);
            }
        }
    },
);

function submitInvite() {
    processing.value = true;
    form.post(`/projects/${props.project.id}/members`, {
        onSuccess: () => {
            successMessage.value = 'Sent invitation';
            form.reset();
            processing.value = false;
            setTimeout(() => {
                successMessage.value = null;
                router.reload();
            }, 1500);
        },
        onError: () => {
            processing.value = false;
        },
    });
}

function updateMemberRole(memberId: number) {
    updatingMemberId.value = memberId;
    const roleForm = useForm({ role: memberRoles.value[memberId] });

    roleForm.patch(`/projects/${props.project.id}/members/${memberId}`, {
        onSuccess: () => {
            successMessage.value = 'Member role updated';
            setTimeout(() => {
                successMessage.value = null;
            }, 2000);
        },
        onError: () => {
            updatingMemberId.value = null;
        },
        onFinish: () => {
            updatingMemberId.value = null;
        },
    });
}

function removeMember(memberId: number) {
    if (!confirm('Remove this member from project?')) {
        return;
    }

    router.delete(`/projects/${props.project.id}/members/${memberId}`, {
        onError: () => {
            removeMemberError.value =
                'Only the project owner can remove members.';
            setTimeout(() => {
                removeMemberError.value = null;
            }, 5000);
        },
    });
}

function cancelInvite(inviteId: number) {
    if (!confirm('Cancel this pending invitation?')) {
        return;
    }

    router.delete(`/projects/${props.project.id}/invites/${inviteId}`, {
        onError: () => {
            cancelInviteError.value =
                'Only the project owner can cancel invitations.';
            setTimeout(() => {
                cancelInviteError.value = null;
            }, 5000);
        },
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
                        :href="`/projects/${project.id}`"
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
                            :class="[
                                'ml-4 inline-flex items-center rounded px-3 py-1.5 text-sm text-white transition',
                                page.url.includes('/members')
                                    ? 'bg-purple-600 hover:bg-purple-500'
                                    : 'bg-slate-700 hover:bg-slate-600',
                            ]"
                        >
                            Members
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto">
            <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
                <h1 class="mb-4 text-2xl font-semibold text-white">
                    Project Members
                </h1>

                <div
                    v-if="successMessage"
                    class="mb-4 flex items-center rounded border border-emerald-600 bg-emerald-900/40 p-4 text-sm text-emerald-200"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="mr-3 h-5 w-5 flex-shrink-0 text-emerald-400"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    {{ successMessage }}
                </div>

                <div
                    v-if="form.errors.email"
                    class="mb-4 rounded border border-red-700 bg-red-900/20 p-3 text-sm text-red-300"
                >
                    {{ form.errors.email }}
                </div>

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
                            required
                            class="rounded border border-slate-700 bg-slate-900 p-2 text-white"
                        >
                            <option value="" disabled>Choose a role</option>
                            <option value="owner">Owner</option>
                            <option value="editor">Editor</option>
                            <option value="viewer">Viewer</option>
                        </select>
                        <button
                            @click.prevent="submitInvite"
                            :disabled="
                                form.processing || !form.email || !form.role
                            "
                            :aria-busy="form.processing"
                            class="inline-flex items-center rounded bg-purple-600 px-3 py-2 text-white hover:bg-purple-500 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span
                                v-if="form.processing"
                                class="mr-2 inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                            ></span>
                            {{ form.processing ? 'Sending...' : 'Send' }}
                        </button>
                    </div>
                    <p class="mt-2 text-sm text-slate-400">
                        Choose a role before sending the invite.
                    </p>
                    <InputError :message="form.errors.role" />
                    <InputError :message="form.errors.email" />
                    <p class="mt-2 text-sm text-slate-400">
                        Invited users receive an email with a link to accept the
                        invite.
                    </p>
                </section>

                <section class="mb-6">
                    <h2 class="mb-2 text-lg font-medium text-white">
                        Active Members
                    </h2>
                    <div
                        v-if="removeMemberError"
                        class="mb-3 flex items-center rounded border border-red-700 bg-red-900/20 p-3 text-sm text-red-300"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="mr-2 h-5 w-5 flex-shrink-0"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        {{ removeMemberError }}
                    </div>
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
                                    <select
                                        v-model="memberRoles[m.id]"
                                        class="rounded border border-slate-700 bg-slate-900 p-2 text-sm text-white"
                                    >
                                        <option value="owner">Owner</option>
                                        <option value="editor">Editor</option>
                                        <option value="viewer">Viewer</option>
                                    </select>
                                    <button
                                        v-if="memberRoles[m.id] !== m.role"
                                        @click="updateMemberRole(m.id)"
                                        :disabled="updatingMemberId === m.id"
                                        class="rounded bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-500 disabled:opacity-50"
                                    >
                                        {{
                                            updatingMemberId === m.id
                                                ? 'Updating...'
                                                : 'Update'
                                        }}
                                    </button>
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
                    <div
                        v-if="cancelInviteError"
                        class="mb-3 flex items-center rounded border border-red-700 bg-red-900/20 p-3 text-sm text-red-300"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="mr-2 h-5 w-5 flex-shrink-0"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        {{ cancelInviteError }}
                    </div>
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
