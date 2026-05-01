<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { store, reorder } from '@/actions/App/Http/Controllers/ColumnController'
import KanbanColumn from './components/KanbanColumn.vue'
import { useForm } from '@inertiajs/vue3'
import { VueDraggable } from 'vue-draggable-plus'

const props = defineProps<{
    project: {
        id: number
        name: string
        description?: string
        columns: Array<{
            id: number
            title: string
            position: number
        }>
    }
}>()

const columns = ref([...props.project.columns])

// Sync columns if project changes (e.g. after Inertia reload)
watch(() => props.project.columns, (newColumns) => {
    columns.value = [...newColumns]
}, { deep: true })

const isAddingColumn = ref(false)
const form = useForm({
    title: ''
})

function submitNewColumn() {
    form.post(store.url(props.project.id), {
        onSuccess: () => {
            isAddingColumn.value = false
            form.reset()
        }
    })
}

function handleReorder() {
    const payload = columns.value.map((column, index) => ({
        id: column.id,
        position: index + 1
    }))

    router.post(reorder.url(props.project.id), {
        columns: payload
    }, {
        preserveScroll: true,
        preserveState: true
    })
}
</script>

<template>
    <Head :title="project.name" />

    <div class="h-screen flex flex-col bg-slate-900 overflow-hidden">
        <!-- Header -->
        <header class="bg-slate-800 shadow-sm border-b border-slate-700 shrink-0">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link href="/dashboard" class="text-slate-400 hover:text-white transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                    </Link>
                    <div>
                        <h2 class="font-bold text-xl leading-tight text-white">
                            {{ project.name }}
                        </h2>
                    </div>
                </div>
            </div>
        </header>

        <!-- Board Area -->
        <main class="flex-1 overflow-x-auto overflow-y-hidden">
            <div class="h-full px-4 sm:px-6 lg:px-8 py-6 inline-flex items-start space-x-6">
                
                <!-- Columns -->
                <VueDraggable
                    v-model="columns"
                    @end="handleReorder"
                    class="flex items-start space-x-6"
                    handle=".column-handle"
                    :animation="150"
                >
                    <KanbanColumn
                        v-for="column in columns"
                        :key="column.id"
                        :column="column"
                        :project-id="project.id"
                    />
                </VueDraggable>

                <!-- Add Column Button -->
                <div class="shrink-0 w-80">
                    <div v-if="!isAddingColumn" 
                         @click="isAddingColumn = true"
                         class="bg-slate-800/50 hover:bg-slate-800 border border-slate-700 border-dashed rounded-lg p-3 cursor-pointer transition-colors flex items-center text-slate-400 hover:text-slate-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Add Column
                    </div>
                    
                    <div v-else class="bg-slate-800 border border-slate-700 rounded-lg p-3">
                        <form @submit.prevent="submitNewColumn">
                            <input 
                                v-model="form.title"
                                type="text"
                                placeholder="Column title..."
                                class="w-full bg-slate-900 border border-slate-700 rounded p-2 text-white text-sm focus:ring-purple-500 focus:border-purple-500 mb-3"
                                autofocus
                            >
                            <div class="flex items-center space-x-2">
                                <button 
                                    type="submit"
                                    :disabled="form.processing || !form.title"
                                    class="bg-purple-600 hover:bg-purple-500 text-white px-3 py-1.5 rounded text-sm font-medium transition disabled:opacity-50"
                                >
                                    Add
                                </button>
                                <button 
                                    type="button"
                                    @click="isAddingColumn = false; form.reset()"
                                    class="text-slate-400 hover:text-slate-300 px-2 py-1.5 text-sm"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>
