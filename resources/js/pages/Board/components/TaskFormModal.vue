<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { store, update, destroy } from '@/actions/App/Http/Controllers/TaskController'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import DeleteModal from '@/Components/DeleteModal.vue'

const props = defineProps<{
    show: boolean
    projectId: number
    columnId?: number
    task?: {
        id: number
        title: string
        description?: string
        priority: 'low' | 'medium' | 'high'
        due_date?: string
    } | null
}>()

const emit = defineEmits<{
    (e: 'close'): void
}>()

const form = useForm({
    title: '',
    description: '',
    priority: 'medium' as 'low' | 'medium' | 'high',
    due_date: '',
})

watch(() => props.show, (showing) => {
    if (showing) {
        if (props.task) {
            form.title = props.task.title
            form.description = props.task.description ?? ''
            form.priority = props.task.priority
            form.due_date = props.task.due_date ?? ''
        } else {
            form.reset()
        }
    }
})

function submit() {
    if (props.task) {
        form.patch(update.url([props.projectId, props.task.id]), {
            onSuccess: () => emit('close'),
            preserveScroll: true
        })
    } else if (props.columnId) {
        form.post(store.url([props.projectId, props.columnId]), {
            onSuccess: () => {
                emit('close')
                form.reset()
            },
            preserveScroll: true
        })
    }
}

const isDeleteModalOpen = ref(false)

function handleDelete() {
    if (!props.task) return
    isDeleteModalOpen.value = true
}
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-backdrop">
            <div v-if="show" class="fixed inset-0 z-[60] bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="emit('close')"></div>
        </Transition>

        <Transition name="modal-panel">
            <div v-if="show" class="fixed inset-0 z-[70] overflow-y-auto pointer-events-none" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-xl bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-700 pointer-events-auto">
                        
                        <form @submit.prevent="submit">
                            <div class="bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <h3 class="text-lg font-semibold leading-6 text-white mb-6">
                                    {{ task ? 'Edit Task' : 'Create Task' }}
                                </h3>

                                <div class="space-y-4">
                                    <div>
                                        <InputLabel for="title" value="Title" />
                                        <TextInput
                                            id="title"
                                            v-model="form.title"
                                            type="text"
                                            class="mt-1 block w-full bg-slate-900 border-slate-700 text-white focus:border-purple-500 focus:ring-purple-500"
                                            placeholder="What needs to be done?"
                                            required
                                            autofocus
                                        />
                                        <InputError class="mt-2" :message="form.errors.title" />
                                    </div>

                                    <div>
                                        <InputLabel for="description" value="Description" />
                                        <textarea
                                            id="description"
                                            v-model="form.description"
                                            rows="4"
                                            class="mt-1 block w-full rounded-md border-slate-700 bg-slate-900 text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm"
                                            placeholder="Add more details..."
                                        ></textarea>
                                        <InputError class="mt-2" :message="form.errors.description" />
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel for="priority" value="Priority" />
                                            <select
                                                id="priority"
                                                v-model="form.priority"
                                                class="mt-1 block w-full rounded-md border-slate-700 bg-slate-900 text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm"
                                            >
                                                <option value="low">Low</option>
                                                <option value="medium">Medium</option>
                                                <option value="high">High</option>
                                            </select>
                                            <InputError class="mt-2" :message="form.errors.priority" />
                                        </div>

                                        <div>
                                            <InputLabel for="due_date" value="Due Date" />
                                            <input
                                                id="due_date"
                                                type="date"
                                                v-model="form.due_date"
                                                class="mt-1 block w-full rounded-md border-slate-700 bg-slate-900 text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm"
                                            />
                                            <InputError class="mt-2" :message="form.errors.due_date" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-900/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-slate-700">
                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="inline-flex w-full justify-center rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500 sm:ml-3 sm:w-auto transition disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Saving...' : 'Save Task' }}
                                </button>
                                
                                <button 
                                    v-if="task"
                                    type="button" 
                                    @click="handleDelete"
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-red-500/10 px-3 py-2 text-sm font-semibold text-red-400 shadow-sm hover:bg-red-500/20 sm:mt-0 sm:w-auto transition mr-auto border border-red-500/20"
                                >
                                    Delete
                                </button>

                                <button 
                                    type="button" 
                                    @click="emit('close')"
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-300 shadow-sm ring-1 ring-inset ring-slate-600 hover:bg-slate-700 sm:mt-0 sm:w-auto transition"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Transition>

        <DeleteModal
            v-if="task"
            :show="isDeleteModalOpen"
            :url="destroy.url([projectId, task.id])"
            title="Delete Task"
            :message="`Are you sure you want to delete '${task.title}'?`"
            @close="isDeleteModalOpen = false"
            :on-success="() => emit('close')"
        />
    </Teleport>
</template>

<style scoped>
.modal-backdrop-enter-active,
.modal-backdrop-leave-active {
    transition: opacity 0.3s ease;
}
.modal-backdrop-enter-from,
.modal-backdrop-leave-to {
    opacity: 0;
}

.modal-panel-enter-active,
.modal-panel-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.modal-panel-enter-from,
.modal-panel-leave-to {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
}
</style>
