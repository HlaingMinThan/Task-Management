<script setup lang="ts">
import { ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { reorder as reorderColumns, store as storeColumn } from '@/actions/App/Http/Controllers/ColumnController'
import { VueDraggable } from 'vue-draggable-plus'
import TaskListGroup from './TaskListGroup.vue'
import TaskFormModal from './TaskFormModal.vue'

const props = defineProps<{
    projectId: number
    columns: Array<{
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
    }>
}>()

const localColumns = ref([...props.columns])

watch(() => props.columns, (newCols) => {
    localColumns.value = [...newCols]
}, { deep: true })

const sortField = ref<'title' | 'priority' | 'due_date'>('title')
const sortDirection = ref<'asc' | 'desc'>('asc')

function toggleSort(field: typeof sortField.value) {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortField.value = field
        sortDirection.value = 'asc'
    }
}

function handleColumnReorder() {
    const payload = localColumns.value.map((column, index) => ({
        id: column.id,
        position: index + 1
    }))

    router.post(reorderColumns.url(props.projectId), {
        columns: payload
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

function openAddModal(columnId: number) {
    selectedTask.value = null
    selectedColumnId.value = columnId
    isTaskModalOpen.value = true
}

// Add Status State
const isAddingStatus = ref(false)
const statusForm = useForm({
    title: ''
})

function submitNewStatus() {
    statusForm.post(storeColumn.url(props.projectId), {
        onSuccess: () => {
            isAddingStatus.value = false
            statusForm.reset()
        }
    })
}
</script>

<template>
    <div class="max-w-6xl mx-auto pb-12">
        
        <!-- Global Headers -->
        <div class="grid grid-cols-12 gap-4 px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-400 border-b border-slate-700/50 mb-4 sticky top-0 bg-slate-900/90 backdrop-blur z-10">
            <div @click="toggleSort('title')" class="col-span-6 cursor-pointer hover:text-white transition-colors flex items-center group">
                <span class="ml-7">Task Name</span>
                <svg v-if="sortField === 'title'" :class="{'rotate-180': sortDirection === 'desc'}" class="h-3 w-3 ml-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
            </div>
            <div @click="toggleSort('priority')" class="col-span-3 cursor-pointer hover:text-white transition-colors flex items-center">
                <span>Priority</span>
                <svg v-if="sortField === 'priority'" :class="{'rotate-180': sortDirection === 'desc'}" class="h-3 w-3 ml-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
            </div>
            <div @click="toggleSort('due_date')" class="col-span-3 cursor-pointer hover:text-white transition-colors flex items-center">
                <span>Due Date</span>
                <svg v-if="sortField === 'due_date'" :class="{'rotate-180': sortDirection === 'desc'}" class="h-3 w-3 ml-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
            </div>
        </div>

        <!-- Groups -->
        <VueDraggable
            v-model="localColumns"
            @end="handleColumnReorder"
            handle=".column-handle"
            :animation="150"
        >
            <TaskListGroup
                v-for="column in localColumns"
                :key="column.id"
                :column="column"
                :project-id="projectId"
                :sort-field="sortField"
                :sort-direction="sortDirection"
                @edit-task="openEditModal"
                @add-task="openAddModal"
            />
        </VueDraggable>

        <div v-if="localColumns.length === 0" class="text-center py-12 text-slate-500">
            No statuses found. Add one below to get started.
        </div>

        <!-- Add Status -->
        <div class="mt-4">
            <div v-if="!isAddingStatus" 
                 @click="isAddingStatus = true"
                 class="flex items-center px-4 py-3 rounded-lg border border-dashed border-slate-700 hover:border-slate-500 text-slate-500 hover:text-slate-300 cursor-pointer transition-colors bg-slate-800/20 hover:bg-slate-800/40">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                <span class="font-medium">Add Status</span>
            </div>
            
            <div v-else class="bg-slate-800 p-4 rounded-lg border border-slate-700 shadow-sm">
                <form @submit.prevent="submitNewStatus" class="flex items-center space-x-3">
                    <input 
                        v-model="statusForm.title"
                        type="text"
                        placeholder="Status name (e.g. In Review, Blocked)..."
                        class="flex-1 bg-slate-900 border border-slate-700 rounded-md px-3 py-2 text-white text-sm focus:ring-purple-500 focus:border-purple-500"
                        autofocus
                    >
                    <button 
                        type="submit"
                        :disabled="statusForm.processing || !statusForm.title"
                        class="bg-purple-600 hover:bg-purple-500 text-white px-4 py-2 rounded-md text-sm font-medium transition disabled:opacity-50"
                    >
                        Save
                    </button>
                    <button 
                        type="button"
                        @click="isAddingStatus = false; statusForm.reset()"
                        class="text-slate-400 hover:text-slate-300 px-2 py-2 text-sm font-medium"
                    >
                        Cancel
                    </button>
                </form>
            </div>
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
