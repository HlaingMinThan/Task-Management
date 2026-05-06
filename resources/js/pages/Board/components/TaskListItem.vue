<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    task: {
        id: number
        title: string
        description?: string
        priority: 'low' | 'medium' | 'high'
        due_date?: string
        position: number
    }
}>()

const priorityClass = computed(() => {
    switch (props.task.priority) {
        case 'high': return 'bg-red-500/10 text-red-400 border-red-500/20'
        case 'medium': return 'bg-orange-500/10 text-orange-400 border-orange-500/20'
        case 'low': return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
        default: return 'bg-slate-500/10 text-slate-400 border-slate-500/20'
    }
})

const formattedDate = computed(() => {
    if (!props.task.due_date) return '-'
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric'
    }).format(new Date(props.task.due_date))
})

const isOverdue = computed(() => {
    if (!props.task.due_date) return false
    return new Date(props.task.due_date) < new Date(new Date().setHours(0, 0, 0, 0))
})

defineEmits(['click'])
</script>

<template>
    <!-- We use grid to align with headers -->
    <div class="grid grid-cols-12 gap-4 items-center px-4 py-3 bg-slate-800 hover:bg-slate-700/50 border-b border-slate-700/50 transition-colors group cursor-pointer" @click="$emit('click', task)">
        <div class="col-span-6 flex items-center min-w-0">
            <div class="task-handle cursor-grab active:cursor-grabbing text-slate-600 hover:text-slate-400 mr-3 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity" @click.stop>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="font-medium text-white text-sm truncate">
                {{ task.title }}
            </div>
        </div>
        
        <div class="col-span-3 flex items-center">
            <span :class="['text-[10px] uppercase tracking-wider font-bold px-2 py-1 rounded border', priorityClass]">
                {{ task.priority }}
            </span>
        </div>
        
        <div class="col-span-3 flex items-center text-sm" :class="isOverdue ? 'text-red-400 font-medium' : 'text-slate-400'">
            <svg v-if="task.due_date" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
            </svg>
            <span v-else class="h-4 w-4 mr-1.5"></span>
            {{ formattedDate }}
        </div>
    </div>
</template>
