<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { store, update } from '@/actions/App/Http/Controllers/ProjectController'
import InputError from './InputError.vue'
import InputLabel from './InputLabel.vue'
import TextInput from './TextInput.vue'

const props = defineProps<{
    show: boolean
    project: { id: number, name: string, description?: string } | null
}>()

const emit = defineEmits<{
    (e: 'close'): void
}>()

const nameInput = ref<HTMLInputElement | null>(null)

const form = useForm({
    name: '',
    description: '',
})

watch(() => props.show, (showing) => {
    if (showing) {
        form.clearErrors()
        form.name = props.project?.name ?? ''
        form.description = props.project?.description ?? ''
        setTimeout(() => nameInput.value?.focus(), 100)
    }
})

function submit() {
    if (props.project) {
        form.put(update.url(props.project.id), {
            onSuccess: () => emit('close'),
        })
    } else {
        form.post(store.url(), {
            onSuccess: () => emit('close'),
        })
    }
}
</script>

<template>
    <Transition name="modal-backdrop">
        <div v-if="show" class="fixed inset-0 z-40 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="emit('close')"></div>
    </Transition>

    <Transition name="modal-panel">
        <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto pointer-events-none" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-xl bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-700 pointer-events-auto">
                    
                    <form @submit.prevent="submit">
                        <div class="bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-lg font-semibold leading-6 text-white" id="modal-title">
                                        {{ project ? 'Edit Project' : 'Create Project' }}
                                    </h3>
                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <InputLabel for="name" value="Name" />
                                            <TextInput
                                                id="name"
                                                ref="nameInput"
                                                v-model="form.name"
                                                type="text"
                                                class="mt-1 block w-full bg-slate-900 border-slate-700 text-white focus:border-purple-500 focus:ring-purple-500"
                                                placeholder="e.g., Marketing Campaign Q4"
                                            />
                                            <InputError class="mt-2" :message="form.errors.name" />
                                        </div>
                                        
                                        <div>
                                            <InputLabel for="description" value="Description (Optional)" />
                                            <textarea
                                                id="description"
                                                v-model="form.description"
                                                rows="3"
                                                class="mt-1 block w-full rounded-md border-slate-700 bg-slate-900 text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm"
                                                placeholder="A brief description of this project"
                                            ></textarea>
                                            <InputError class="mt-2" :message="form.errors.description" />
                                        </div>
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
                                {{ form.processing ? 'Saving...' : 'Save' }}
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
