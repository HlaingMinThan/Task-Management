<script setup lang="ts">
const props = defineProps<{
    project: {
        id: number
        name: string
        description?: string
        tasks_count?: number
        updated_at: string
    }
}>()

const emit = defineEmits<{
    (e: 'edit', project: typeof props.project): void
    (e: 'delete', project: typeof props.project): void
}>()

const formattedDate = new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
}).format(new Date(props.project.updated_at))
</script>

<template>
    <div class="bg-slate-800 rounded-lg shadow border border-slate-700 overflow-hidden hover:border-purple-500 transition-colors flex flex-col h-full">
        <div class="p-5 flex-1">
            <h3 class="text-lg font-semibold text-white mb-1">
                {{ project.name }}
            </h3>
            <p v-if="project.description" class="text-slate-400 text-sm line-clamp-2 mb-4">
                {{ project.description }}
            </p>
            
            <div class="flex items-center text-sm text-slate-400 space-x-4 mt-auto pt-4">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-purple-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                    </svg>
                    {{ project.tasks_count ?? 0 }} tasks
                </div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Updated {{ formattedDate }}
                </div>
            </div>
        </div>
        
        <div class="bg-slate-900/50 px-5 py-3 border-t border-slate-700 flex justify-end space-x-3">
            <button 
                @click="emit('edit', project)"
                class="text-sm font-medium text-purple-400 hover:text-purple-300 transition"
            >
                Edit
            </button>
            <button 
                @click="emit('delete', project)"
                class="text-sm font-medium text-red-400 hover:text-red-300 transition"
            >
                Delete
            </button>
        </div>
    </div>
</template>
