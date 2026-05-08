<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    task: {
        id: number
        title: string
        description?: string
        priority: 'low' | 'medium' | 'high'
        due_date?: string
        assignees?: Array<{
            id: number
            user?: {
                id: number
                name: string
            }
        }>
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
    if (!props.task.due_date) return null
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric'
    }).format(new Date(props.task.due_date))
})

const isOverdue = computed(() => {
    if (!props.task.due_date) return false
    return new Date(props.task.due_date) < new Date(new Date().setHours(0, 0, 0, 0))
})

const assigneesList = computed(() => {
    return (props.task.assignees ?? []).filter(a => a && a.user).map(a => a.user)
})
</script>

<template>
    <div class="bg-slate-700/50 hover:bg-slate-700 p-3 rounded-lg border border-slate-600 shadow-sm transition-all cursor-pointer group mb-2">
        <div class="flex justify-between items-start mb-2">
            <span :class="['text-[10px] uppercase tracking-wider font-bold px-1.5 py-0.5 rounded border', priorityClass]">
                {{ task.priority }}
            </span>

            <div v-if="assigneesList.length" class="flex items-center -space-x-1">
                <template v-for="(user, index) in assigneesList.slice(0, 3)" :key="user.id ?? index">
                    <div class="h-6 w-6 rounded-full bg-slate-600 flex items-center justify-center text-[11px] font-medium text-white border-2 border-slate-700" :title="user.name">
                        {{ user.name ? user.name.charAt(0) : '' }}
                    </div>
                </template>

                <div v-if="assigneesList.length > 3" class="h-6 w-6 rounded-full bg-slate-600 flex items-center justify-center text-[11px] font-medium text-white border-2 border-slate-700 text-xs">
                    +{{ assigneesList.length - 3 }}
                </div>
            </div>
        </div>
        
        <h4 class="text-sm font-medium text-white mb-2 leading-tight">
            {{ task.title }}
        </h4>
        
        <div v-if="formattedDate" class="flex items-center text-[11px]" :class="isOverdue ? 'text-red-400' : 'text-slate-400'">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
            </svg>
            {{ formattedDate }}
        </div>
    </div>
</template>
