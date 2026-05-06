<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { reorder as reorderTasks } from '@/actions/App/Http/Controllers/TaskController'
import { VueDraggable } from 'vue-draggable-plus'
import TaskListItem from './TaskListItem.vue'

const props = defineProps<{
    projectId: number
    column: {
        id: number
        title: string
        position: number
        tasks: Array<{
            id: number
            title: string
            description?: string
            priority: 'low' | 'medium' | 'high'
            due_date?: string
            position: number
        }>
    }
    sortField: 'title' | 'priority' | 'due_date'
    sortDirection: 'asc' | 'desc'
}>()

const emit = defineEmits(['editTask', 'addTask'])

const tasks = computed({
    get: () => {
        let sorted = [...(props.column.tasks || [])]
        
        sorted.sort((a, b) => {
            let valA: any = a[props.sortField]
            let valB: any = b[props.sortField]

            if (props.sortField === 'priority') {
                const priorityWeight = { high: 3, medium: 2, low: 1 }
                valA = priorityWeight[a.priority as keyof typeof priorityWeight] || 0
                valB = priorityWeight[b.priority as keyof typeof priorityWeight] || 0
            }

            if (valA === null || valA === undefined) valA = ''
            if (valB === null || valB === undefined) valB = ''

            if (valA < valB) return props.sortDirection === 'asc' ? -1 : 1
            if (valA > valB) return props.sortDirection === 'asc' ? 1 : -1
            return 0
        })
        
        return sorted
    },
    set: (value) => {
        // Handle drag and drop local state if needed
    }
})

function handleTaskReorder(event: any) {
    // When a task is moved to this column or reordered within it
    // event.to is the HTML element, but vue-draggable-plus fires change event
    // Wait, with vue-draggable-plus, we should bind to a local ref, and on end sync.
    // Given the sorting, if it's sorted by anything other than default, drag and drop might be weird.
    // But since the user wants it to only mind their respective status, let's keep a local ref that syncs.
}

const localTasks = ref([...props.column.tasks])

// Update localTasks when props change (e.g. from sorting or Inertia props)
// We need to maintain sort order but also allow dragging. 
// If it's sorted by title, dragging to reorder doesn't make sense. But we'll allow it and it overrides sort until re-sorted.
import { watch } from 'vue'
watch(() => props.column.tasks, (newTasks) => {
    // Re-apply sort
    let sorted = [...(newTasks || [])]
    sorted.sort((a, b) => {
        let valA: any = a[props.sortField]
        let valB: any = b[props.sortField]
        if (props.sortField === 'priority') {
            const w = { high: 3, medium: 2, low: 1 }
            valA = w[a.priority as keyof typeof w] || 0
            valB = w[b.priority as keyof typeof w] || 0
        }
        if (valA === null || valA === undefined) valA = ''
        if (valB === null || valB === undefined) valB = ''
        if (valA < valB) return props.sortDirection === 'asc' ? -1 : 1
        if (valA > valB) return props.sortDirection === 'asc' ? 1 : -1
        return 0
    })
    localTasks.value = sorted
}, { deep: true })

watch([() => props.sortField, () => props.sortDirection], () => {
    let sorted = [...localTasks.value]
    sorted.sort((a, b) => {
        let valA: any = a[props.sortField]
        let valB: any = b[props.sortField]
        if (props.sortField === 'priority') {
            const w = { high: 3, medium: 2, low: 1 }
            valA = w[a.priority as keyof typeof w] || 0
            valB = w[b.priority as keyof typeof w] || 0
        }
        if (valA === null || valA === undefined) valA = ''
        if (valB === null || valB === undefined) valB = ''
        if (valA < valB) return props.sortDirection === 'asc' ? -1 : 1
        if (valA > valB) return props.sortDirection === 'asc' ? 1 : -1
        return 0
    })
    localTasks.value = sorted
})

function onReorderEnd() {
    const payload = localTasks.value.map((task, index) => ({
        id: task.id,
        position: index + 1
    }))

    router.post(reorderTasks.url(props.projectId), {
        column_id: props.column.id,
        tasks: payload
    }, {
        preserveScroll: true,
        preserveState: true
    })
}

const isCollapsed = ref(false)
</script>

<template>
    <div class="mb-6 bg-slate-800/30 rounded-lg border border-slate-700/50 overflow-hidden">
        <!-- Group Header -->
        <div class="flex items-center px-4 py-3 bg-slate-900/50 border-b border-slate-700/50 group/header">
            <div class="column-handle cursor-grab active:cursor-grabbing text-slate-500 hover:text-slate-300 mr-2 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </div>
            
            <button @click="isCollapsed = !isCollapsed" class="mr-2 text-slate-400 hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform" :class="{ '-rotate-90': isCollapsed }" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>

            <h3 class="font-bold text-base text-white mr-3">{{ column.title }}</h3>
            <span class="text-xs font-medium text-slate-500 bg-slate-800 px-2 py-0.5 rounded-full">{{ localTasks.length }}</span>
        </div>

        <!-- Group Body (Tasks) -->
        <div v-show="!isCollapsed">
            <VueDraggable
                v-model="localTasks"
                group="tasks"
                @end="onReorderEnd"
                @add="onReorderEnd"
                handle=".task-handle"
                :animation="150"
                class="min-h-[10px]"
            >
                <TaskListItem
                    v-for="task in localTasks"
                    :key="task.id"
                    :task="task"
                    @click="emit('editTask', task)"
                />
            </VueDraggable>
            
            <!-- Add Task Button for this group -->
            <div class="px-4 py-2 bg-slate-800 hover:bg-slate-700/50 transition-colors border-t border-slate-700/50">
                <button 
                    @click="emit('addTask', column.id)"
                    class="flex items-center text-sm text-slate-400 hover:text-white transition-colors py-1"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    New Task
                </button>
            </div>
        </div>
    </div>
</template>
