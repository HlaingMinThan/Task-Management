<script lang="ts">
import { defineComponent, ref, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

export default defineComponent({
    props: {
        show: { type: Boolean, required: true },
        projectId: { type: Number, required: true },
        taskId: { type: Number, required: true },
        initialAssignees: { type: Array, default: () => [] }
    },
    emits: ['close'],
    setup(props, { emit }) {
        const search = ref('')
        const users = ref<Array<{ id: number, name: string, email?: string }>>([])
        const loading = ref(false)
        const page = ref(1)
        const perPage = 20
        const selected = ref<Record<number, { id: number, name: string }>>({})
        let debounceTimer: any = null

        onMounted(() => {
            if (props.initialAssignees) {
                (props.initialAssignees as any).forEach((a: any) => { if (a) selected.value[a.id] = a })
            }
        })

        watch(() => props.show, (val) => {
            if (val) {
                loadUsers()
            }
        })

        watch(search, () => {
            clearTimeout(debounceTimer)
            debounceTimer = setTimeout(() => {
                page.value = 1
                loadUsers()
            }, 300)
        })

        async function loadUsers() {
            loading.value = true
            try {
                const url = `/projects/${props.projectId}/users?query=${encodeURIComponent(String(search.value))}&page=${page.value}`
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } })
                const data = await res.json()
                users.value = data.data
            } finally {
                loading.value = false
            }
        }

        function toggleSelect(user: any) {
            if (selected.value[user.id]) {
                delete selected.value[user.id]
            } else {
                selected.value[user.id] = user
            }
        }

        function isSelected(user: any) {
            return !!selected.value[user.id]
        }

        async function confirm() {
            const userIds = Object.keys(selected.value).map(k => Number(k))
            if (!props.taskId || userIds.length === 0) {
                emit('close')
                return
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

            await fetch(`/projects/${props.projectId}/tasks/${props.taskId}/assignees`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || ''
                },
                body: JSON.stringify({ user_ids: userIds })
            })

            // reload to reflect changes
            router.reload()
        }

        return {
            search,
            users,
            loading,
            page,
            perPage,
            selected,
            toggleSelect,
            isSelected,
            confirm
        }
    }
})
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-backdrop">
            <div v-if="show" class="fixed inset-0 z-[80] bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="emit('close')"></div>
        </Transition>

        <Transition name="modal-panel">
            <div v-if="show" class="fixed inset-0 z-[90] overflow-y-auto pointer-events-none" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-xl bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-700 pointer-events-auto">
                        <div class="px-4 py-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-semibold text-white">Assign users</h3>
                                <div class="text-sm text-slate-400">Selected: {{ Object.keys(selected).length }}</div>
                            </div>

                            <div class="mb-3">
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search users..."
                                    class="w-full rounded bg-slate-900 px-3 py-2 text-white text-sm border border-slate-700"
                                />
                            </div>

                            <div class="max-h-60 overflow-y-auto">
                                <div v-if="loading" class="text-sm text-slate-400">Loading...</div>
                                <div v-else>
                                    <div v-for="user in users" :key="user.id" class="flex items-center justify-between p-2 hover:bg-slate-700/50 rounded">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-slate-600 flex items-center justify-center text-xs text-white mr-3">{{ user.name ? user.name.charAt(0) : '' }}</div>
                                            <div>
                                                <div class="text-sm text-white">{{ user.name }}</div>
                                                <div class="text-xs text-slate-400">{{ user.email }}</div>
                                            </div>
                                        </div>
                                        <div>
                                            <button @click.prevent="toggleSelect(user)" class="px-2 py-1 rounded border text-sm" :class="isSelected(user) ? 'bg-purple-600 text-white' : 'text-slate-300 border-slate-700'">
                                                {{ isSelected(user) ? 'Selected' : 'Add' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <template v-for="(s, id) in selected" :key="id">
                                    <div class="bg-slate-700 px-2 py-1 rounded text-xs flex items-center gap-2">
                                        <span>{{ s.name }}</span>
                                        <button @click.prevent="() => toggleSelect(s)" class="text-slate-400">×</button>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-4 flex justify-end gap-2">
                                <button @click="emit('close')" class="px-3 py-2 rounded bg-slate-800 text-slate-300">Cancel</button>
                                <button @click.prevent="confirm" class="px-3 py-2 rounded bg-purple-600 text-white">Assign</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
