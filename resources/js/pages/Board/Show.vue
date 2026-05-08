<script setup lang="ts">
import { Head, Link, router, useRemember } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import type { Ref } from 'vue';
import { computed, ref, watch } from 'vue';
import { VueDraggable } from 'vue-draggable-plus';
import {
    store,
    reorder,
} from '@/actions/App/Http/Controllers/ColumnController';
import { reorder as reorderTasks } from '@/actions/App/Http/Controllers/TaskController';
import KanbanColumn from './components/KanbanColumn.vue';
import TaskList from './components/TaskList.vue';

type Task = {
    id: number;
    column_id?: number;
    title: string;
    description?: string;
    priority: 'low' | 'medium' | 'high';
    due_date?: string;
    position: number;
};

type Column = {
    id: number;
    title: string;
    position: number;
    tasks: Array<Task>;
};

const props = defineProps<{
    project: {
        id: number;
        name: string;
        description?: string;
        columns: Array<Column>;
    };
}>();

function cloneColumns(columns: Array<Column>) {
    return columns.map((column) => ({
        ...column,
        tasks: [...(column.tasks ?? [])],
    }));
}

const columns = ref(cloneColumns(props.project.columns));

// Sync columns if project changes (e.g. after Inertia reload)
watch(
    () => props.project.columns,
    (newColumns) => {
        columns.value = cloneColumns(newColumns);
    },
    { deep: true },
);

const rememberedViewMode = useRemember(
    { mode: 'board' as 'board' | 'list' },
    'project-view-mode',
) as Ref<{ mode: 'board' | 'list' }>;
const viewMode = computed({
    get: () => rememberedViewMode.value.mode,
    set: (mode: 'board' | 'list') => {
        rememberedViewMode.value.mode = mode;
    },
});

const isAddingColumn = ref(false);
const form = useForm({
    title: '',
});

function submitNewColumn() {
    form.post(store.url(props.project.id), {
        onSuccess: () => {
            isAddingColumn.value = false;
            form.reset();
        },
    });
}

function handleReorder() {
    const payload = columns.value.map((column, index) => ({
        id: column.id,
        position: index + 1,
    }));

    router.post(
        reorder.url(props.project.id),
        {
            columns: payload,
        },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
}

function syncColumnTasks(columnId: number, updatedTasks: Array<Task>) {
    const updatedTaskIds = new Set(updatedTasks.map((task) => task.id));

    columns.value = columns.value.map((column) => {
        if (column.id === columnId) {
            return {
                ...column,
                tasks: updatedTasks.map((task, index) => ({
                    ...task,
                    column_id: columnId,
                    position: index + 1,
                })),
            };
        }

        return {
            ...column,
            tasks: column.tasks.filter((task) => !updatedTaskIds.has(task.id)),
        };
    });
}

function persistTaskOrder(columnId: number, tasks: Array<Task>) {
    router.post(
        reorderTasks.url(props.project.id),
        {
            column_id: columnId,
            tasks: tasks.map((task, index) => ({
                id: task.id,
                position: index + 1,
            })),
        },
        {
            preserveScroll: true,
            preserveState: true,
            onError: () => {
                router.reload({ only: ['project'] });
            },
        },
    );
}

function handleTaskListChange(
    columnId: number,
    tasks: Array<Task>,
    persist = true,
) {
    syncColumnTasks(columnId, tasks);

    if (persist) {
        persistTaskOrder(columnId, tasks);
    }
}
</script>

<template>
    <Head :title="project.name" />

    <div class="flex h-screen flex-col overflow-hidden bg-slate-900">
        <!-- Header -->
        <header
            class="shrink-0 border-b border-slate-700 bg-slate-800 shadow-sm"
        >
            <div
                class="mx-auto flex h-16 max-w-full items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <div class="flex items-center space-x-4">
                    <Link
                        href="/dashboard"
                        class="text-slate-400 transition hover:text-white"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </Link>
                    <div>
                        <h2 class="text-xl leading-tight font-bold text-white">
                            {{ project.name }}
                        </h2>
                    </div>
                </div>

                <div
                    class="flex items-center space-x-1 rounded-lg border border-slate-700 bg-slate-900/50 p-1"
                >
                    <button
                        @click="viewMode = 'board'"
                        :class="{
                            'bg-slate-700 text-white shadow-sm':
                                viewMode === 'board',
                            'text-slate-400 hover:text-slate-300':
                                viewMode !== 'board',
                        }"
                        class="rounded px-3 py-1.5 text-sm font-medium transition-all"
                    >
                        Board
                    </button>
                    <button
                        @click="viewMode = 'list'"
                        :class="{
                            'bg-slate-700 text-white shadow-sm':
                                viewMode === 'list',
                            'text-slate-400 hover:text-slate-300':
                                viewMode !== 'list',
                        }"
                        class="rounded px-3 py-1.5 text-sm font-medium transition-all"
                    >
                        List
                    </button>
                </div>
            </div>
        </header>

        <!-- Board Area -->
        <main class="flex-1 overflow-x-auto overflow-y-hidden">
            <template v-if="viewMode === 'board'">
                <div
                    class="inline-flex h-full items-start space-x-6 px-4 py-6 sm:px-6 lg:px-8"
                >
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
                            @task-list-change="handleTaskListChange"
                        />
                    </VueDraggable>

                    <!-- Add Column Button -->
                    <div class="w-80 shrink-0">
                        <div
                            v-if="!isAddingColumn"
                            @click="isAddingColumn = true"
                            class="flex cursor-pointer items-center rounded-lg border border-dashed border-slate-700 bg-slate-800/50 p-3 text-slate-400 transition-colors hover:bg-slate-800 hover:text-slate-300"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="mr-2 h-5 w-5"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            Add Column
                        </div>

                        <div
                            v-else
                            class="rounded-lg border border-slate-700 bg-slate-800 p-3"
                        >
                            <form @submit.prevent="submitNewColumn">
                                <input
                                    v-model="form.title"
                                    type="text"
                                    placeholder="Column title..."
                                    class="mb-3 w-full rounded border border-slate-700 bg-slate-900 p-2 text-sm text-white focus:border-purple-500 focus:ring-purple-500"
                                    autofocus
                                />
                                <div class="flex items-center space-x-2">
                                    <button
                                        type="submit"
                                        :disabled="
                                            form.processing || !form.title
                                        "
                                        class="rounded bg-purple-600 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-purple-500 disabled:opacity-50"
                                    >
                                        Add
                                    </button>
                                    <button
                                        type="button"
                                        @click="
                                            isAddingColumn = false;
                                            form.reset();
                                        "
                                        class="px-2 py-1.5 text-sm text-slate-400 hover:text-slate-300"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="h-full overflow-y-auto px-4 py-6 sm:px-6 lg:px-8">
                    <TaskList
                        :columns="columns"
                        :project-id="project.id"
                        @task-list-change="handleTaskListChange"
                    />
                </div>
            </template>
        </main>
    </div>
</template>
