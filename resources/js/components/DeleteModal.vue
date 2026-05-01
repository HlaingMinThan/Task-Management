<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
    show: boolean
    url: string | null
    title?: string
    message?: string
}>()

const emit = defineEmits<{
    (e: 'close'): void
}>()

const form = useForm({})

function deleteItem() {
    if (!props.url) return
    
    form.delete(props.url, {
        onSuccess: () => emit('close'),
    })
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
                    <div class="relative transform overflow-hidden rounded-xl bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-700 pointer-events-auto">
                        
                        <div class="bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100/10 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <h3 class="text-lg font-semibold leading-6 text-white" id="modal-title">
                                        {{ title ?? 'Confirm Deletion' }}
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-slate-400">
                                            {{ message ?? 'Are you sure you want to delete this item? This action cannot be undone.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-slate-900/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-slate-700">
                            <button 
                                type="button" 
                                @click="deleteItem"
                                :disabled="form.processing"
                                class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto transition disabled:opacity-50"
                            >
                                {{ form.processing ? 'Deleting...' : 'Delete' }}
                            </button>
                            <button 
                                type="button" 
                                @click="emit('close')"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-300 shadow-sm ring-1 ring-inset ring-slate-600 hover:bg-slate-700 sm:mt-0 sm:w-auto transition"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
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
