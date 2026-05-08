<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    task: {
        id: number
        title: string
        description?: string
        priority: 'low' | 'medium' | 'high'
        status: string
        due_date?: string
        completed_at?: string
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
    if (!props.task.due_date || props.task.status === 'done') return false
    const now = new Date()
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
    const due = new Date(props.task.due_date)
    const dueDateOnly = new Date(due.getFullYear(), due.getMonth(), due.getDate())
    return dueDateOnly.getTime() < today.getTime()
})

const isDueToday = computed(() => {
    if (!props.task.due_date || props.task.status === 'done') return false
    const now = new Date()
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
    const due = new Date(props.task.due_date)
    const dueDateOnly = new Date(due.getFullYear(), due.getMonth(), due.getDate())
    return dueDateOnly.getTime() === today.getTime()
})

const dateText = computed(() => {
    if (props.task.status === 'done' && props.task.completed_at) {
        const completed = new Date(props.task.completed_at)
        const now = new Date()
        
        // Strip time for day comparison
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
        const compDate = new Date(completed.getFullYear(), completed.getMonth(), completed.getDate())
        
        const diffTime = today.getTime() - compDate.getTime()
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24))
        
        const exactDate = new Intl.DateTimeFormat('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        }).format(completed)
        
        if (diffDays === 0) {
            return `Today (${exactDate})`
        } else if (diffDays === 1) {
            return `1 day ago (${exactDate})`
        } else {
            return `${diffDays} days ago (${exactDate})`
        }
    }

    if (!props.task.due_date) return null
    
    const now = new Date()
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
    const due = new Date(props.task.due_date)
    const dueDateOnly = new Date(due.getFullYear(), due.getMonth(), due.getDate())
    
    const diffTime = dueDateOnly.getTime() - today.getTime()
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24))
    
    if (diffDays === 0) {
        return 'Due today'
    } else if (diffDays > 0) {
        return `Due in ${diffDays} ${diffDays === 1 ? 'day' : 'days'}`
    } else {
        const days = Math.abs(diffDays)
        const exactDate = new Intl.DateTimeFormat('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        }).format(due)

        let relativeStr = ''
        if (days >= 365) {
            const years = Math.round(days / 365)
            relativeStr = `${years} ${years === 1 ? 'year' : 'years'} overdue`
        } else if (days >= 30) {
            const months = Math.floor(days / 30)
            relativeStr = `${months} ${months === 1 ? 'month' : 'months'} overdue`
        } else if (days >= 7) {
            const weeks = Math.floor(days / 7)
            relativeStr = `${weeks} ${weeks === 1 ? 'week' : 'weeks'} overdue`
        } else {
            relativeStr = `${days} ${days === 1 ? 'day' : 'days'} overdue`
        }

        return `${relativeStr} (Due ${exactDate})`
    }
})
</script>

<template>
    <div :class="[
        'bg-slate-700/50 hover:bg-slate-700 p-3 rounded-lg shadow-sm transition-all cursor-pointer group mb-2',
        isOverdue ? 'border border-red-500/50 shadow-[0_0_8px_rgba(239,68,68,0.15)]' : 
        task.status === 'done' ? 'border border-slate-600 opacity-90' : 'border border-slate-600'
    ]">
        <div class="flex justify-between items-start mb-2">
            <span :class="['text-[10px] uppercase tracking-wider font-bold px-1.5 py-0.5 rounded border', priorityClass]">
                {{ task.priority }}
            </span>
        </div>
        
        <h4 class="text-sm font-medium text-white mb-2 leading-tight">
            {{ task.title }}
        </h4>
        
        <div v-if="task.status === 'done' && task.completed_at" class="flex items-center text-[11px] text-emerald-400 font-medium bg-emerald-500/10 px-2 py-1.5 rounded border border-emerald-500/20 mt-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span class="truncate">{{ dateText }}</span>
        </div>
        <div v-else-if="isOverdue" class="flex items-center text-[11px] text-red-400 font-medium bg-red-500/10 px-2 py-1.5 rounded border border-red-500/20 mt-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
            </svg>
            <span class="truncate">{{ dateText }}</span>
        </div>
        <div v-else-if="isDueToday" class="flex items-center text-[11px] text-orange-400 font-medium mt-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
            </svg>
            {{ dateText }}
        </div>
        <div v-else-if="task.due_date" class="flex items-center text-[11px] text-slate-400 mt-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
            </svg>
            {{ dateText }}
        </div>
    </div>
</template>
