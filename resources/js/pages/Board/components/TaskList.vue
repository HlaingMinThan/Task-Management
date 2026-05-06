<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { update } from '@/actions/App/Http/Controllers/TaskController'
import TaskFormModal from './TaskFormModal.vue'

const props = defineProps<{
    projectId: number
    columns: Array<{
        id: number
        title: string
    }>
    tasks: Array<{
        id: number
        title: string
        description?: string
        priority: 'low' | 'medium' | 'high'
        due_date?: string
        position: number
        column_id: number
        column_title: string
    }>
}>()

const sortField = ref<'title' | 'column_title' | 'priority' | 'due_date'>('title')
const sortDirection = ref<'asc' | 'desc'>('asc')

const sortedTasks = computed(() => {
    return [...props.tasks].sort((a, b) => {
        let valA: any = a[sortField.value]
        let valB: any = b[sortField.value]

        // Handle priorities specifically
        if (sortField.value === 'priority') {
            const priorityWeight = { high: 3, medium: 2, low: 1 }
            valA = priorityWeight[a.priority as keyof typeof priorityWeight] || 0
            valB = priorityWeight[b.priority as keyof typeof priorityWeight] || 0
        }

        // Handle nulls
        if (valA === null || valA === undefined) valA = ''
        if (valB === null || valB === undefined) valB = ''

        if (valA < valB) return sortDirection.value === 'asc' ? -1 : 1
        if (valA > valB) return sortDirection.value === 'asc' ? 1 : -1
        return 0
    })
})

function toggleSort(field: typeof sortField.value) {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortField.value = field
        sortDirection.value = 'asc'
    }
}

function updateTaskColumn(task: any, newColumnId: number) {
    router.patch(update.url([props.projectId, task.id]), {
        column_id: newColumnId,
        title: task.title,
        priority: task.priority,
        description: task.description,
        due_date: task.due_date
    }, {
        preserveScroll: true,
        preserveState: true
    })
}

// Modal State
const isTaskModalOpen = ref(false)
const selectedTask = ref<any>(null)
const selectedColumnId = ref<number | undefined>(undefined)

function openEditModal(task: any) {
    selectedTask.value = task
    selectedColumnId.value = task.column_id
    isTaskModalOpen.value = true
}

function getPriorityClass(priority: string) {
    switch (priority) {
        case 'high': return 'bg-red-500/10 text-red-400 border-red-500/20'
        case 'medium': return 'bg-orange-500/10 text-orange-400 border-orange-500/20'
        case 'low': return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
        default: return 'bg-slate-500/10 text-slate-400 border-slate-500/20'
    }
}

function formatDate(dateString?: string) {
    if (!dateString) return '-'
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric'
    }).format(new Date(dateString))
}

function isOverdue(dateString?: string) {
    if (!dateString) return false
    return new Date(dateString) < new Date(new Date().setHours(0, 0, 0, 0))
}
</script>

<template>
    <div class="bg-slate-800 rounded-lg border border-slate-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-900/50 text-xs uppercase text-slate-400 border-b border-slate-700">
                    <tr>
                        <th @click="toggleSort('title')" class="px-6 py-4 cursor-pointer hover:text-white transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>Task Name</span>
                                <svg v-if="sortField === 'title'" :class="{'rotate-180': sortDirection === 'desc'}" class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                            </div>
                        </th>
                        <th @click="toggleSort('column_title')" class="px-6 py-4 cursor-pointer hover:text-white transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>Status</span>
                                <svg v-if="sortField === 'column_title'" :class="{'rotate-180': sortDirection === 'desc'}" class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                            </div>
                        </th>
                        <th @click="toggleSort('priority')" class="px-6 py-4 cursor-pointer hover:text-white transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>Priority</span>
                                <svg v-if="sortField === 'priority'" :class="{'rotate-180': sortDirection === 'desc'}" class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                            </div>
                        </th>
                        <th @click="toggleSort('due_date')" class="px-6 py-4 cursor-pointer hover:text-white transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>Due Date</span>
                                <svg v-if="sortField === 'due_date'" :class="{'rotate-180': sortDirection === 'desc'}" class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50 bg-slate-800">
                    <tr v-for="task in sortedTasks" :key="task.id" class="hover:bg-slate-700/50 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap cursor-pointer" @click="openEditModal(task)">
                            <div class="font-medium text-white">{{ task.title }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <select 
                                :value="task.column_id"
                                @change="updateTaskColumn(task, parseInt(($event.target as HTMLSelectElement).value))"
                                class="bg-slate-900 border border-slate-700 text-slate-300 text-xs rounded focus:ring-purple-500 focus:border-purple-500 block w-full p-1.5"
                                @click.stop
                            >
                                <option v-for="col in columns" :key="col.id" :value="col.id">
                                    {{ col.title }}
                                </option>
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap cursor-pointer" @click="openEditModal(task)">
                            <span :class="['text-[10px] uppercase tracking-wider font-bold px-2 py-1 rounded border', getPriorityClass(task.priority)]">
                                {{ task.priority }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap cursor-pointer" @click="openEditModal(task)">
                            <div class="flex items-center text-sm" :class="isOverdue(task.due_date) ? 'text-red-400 font-medium' : 'text-slate-400'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                </svg>
                                {{ formatDate(task.due_date) }}
                            </div>
                        </td>
                    </tr>
                    <tr v-if="sortedTasks.length === 0">
                        <td colspan="4" class="px-6 py-8 text-center text-slate-500 text-sm">
                            No tasks found in this project.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <TaskFormModal
            :show="isTaskModalOpen"
            :project-id="projectId"
            :column-id="selectedColumnId"
            :task="selectedTask"
            @close="isTaskModalOpen = false"
        />
    </div>
</template>
