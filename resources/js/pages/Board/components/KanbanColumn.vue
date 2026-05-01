<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { update, destroy } from '@/actions/App/Http/Controllers/ColumnController'
import DeleteModal from '@/Components/DeleteModal.vue'

const props = defineProps<{
    projectId: number
    column: {
        id: number
        title: string
        position: number
    }
}>()

const isEditing = ref(false)
const inputRef = ref<HTMLInputElement | null>(null)

const form = useForm({
    title: props.column.title
})

function startEditing() {
    isEditing.value = true
    form.title = props.column.title
    setTimeout(() => {
        inputRef.value?.focus()
        inputRef.value?.select()
    }, 50)
}

function saveEdit() {
    if (!form.title || form.title === props.column.title) {
        isEditing.value = false
        form.title = props.column.title
        return
    }

    form.patch(update.url([props.projectId, props.column.id]), {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false
        }
    })
}

const isDeleteModalOpen = ref(false)

function confirmDelete() {
    isDeleteModalOpen.value = true
}
</script>

<template>
    <div class="shrink-0 w-80 bg-slate-800 rounded-lg flex flex-col max-h-full border border-slate-700 shadow-sm">
        <!-- Column Header -->
        <div class="p-3 border-b border-slate-700 flex justify-between items-start group">
            <div class="flex items-center flex-1 mr-2 min-w-0">
                <!-- Drag Handle -->
                <div class="column-handle cursor-grab active:cursor-grabbing text-slate-500 hover:text-slate-300 mr-2 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>

                <div class="flex-1 min-w-0">
                    <form v-if="isEditing" @submit.prevent="saveEdit">
                        <input 
                            ref="inputRef"
                            v-model="form.title"
                            type="text"
                            @blur="saveEdit"
                            @keyup.escape="isEditing = false"
                            class="w-full bg-slate-900 border-purple-500 rounded px-2 py-1 text-sm font-semibold text-white focus:ring-purple-500 focus:border-purple-500"
                        >
                    </form>
                    <div 
                        v-else 
                        @click="startEditing"
                        class="px-2 py-1 text-sm font-semibold text-white cursor-text rounded hover:bg-slate-700 transition truncate"
                    >
                        {{ column.title }}
                    </div>
                </div>
            </div>
            
            <button 
                @click="confirmDelete"
                class="text-slate-500 hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity p-1 rounded hover:bg-slate-700"
                title="Delete Column"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <!-- Column Body (Tasks will go here) -->
        <div class="p-2 flex-1 overflow-y-auto min-h-[100px]">
            <!-- Placeholder for tasks -->
            <div class="text-xs text-slate-500 text-center mt-4">
                No tasks yet
            </div>
        </div>
        
        <!-- Add Task Button -->
        <div class="p-2 border-t border-slate-700">
            <button class="w-full flex items-center justify-center py-2 px-4 rounded text-sm text-slate-400 hover:text-white hover:bg-slate-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Add Task
            </button>
        </div>

        <DeleteModal
            :show="isDeleteModalOpen"
            :url="destroy.url([projectId, column.id])"
            title="Delete Column"
            :message="`Are you sure you want to delete '${column.title}'?`"
            @close="isDeleteModalOpen = false"
        />
    </div>
</template>
