<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { VueDraggable } from 'vue-draggable-plus';
import {
    reorder as reorderColumns,
    store as storeColumn,
} from '@/actions/App/Http/Controllers/ColumnController';
import TaskFormModal from './TaskFormModal.vue';
import TaskListGroup from './TaskListGroup.vue';

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
    projectId: number;
    columns: Array<Column>;
}>();

const emit = defineEmits<{
    (
        e: 'taskListChange',
        columnId: number,
        tasks: Array<Task>,
        persist?: boolean,
    ): void;
}>();

function cloneColumns(columns: Array<Column>) {
    return columns.map((column) => ({
        ...column,
        tasks: [...(column.tasks ?? [])],
    }));
}

function columnTaskKey(column: Column) {
    return `${column.id}:${column.tasks.map((task) => task.id).join(',')}`;
}

const localColumns = ref(cloneColumns(props.columns));

watch(
    () => props.columns,
    (newCols) => {
        localColumns.value = cloneColumns(newCols);
    },
    { deep: true },
);

const sortField = ref<'title' | 'priority' | 'due_date'>('title');
const sortDirection = ref<'asc' | 'desc'>('asc');

function toggleSort(field: typeof sortField.value) {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
}

function handleColumnReorder() {
    const payload = localColumns.value.map((column, index) => ({
        id: column.id,
        position: index + 1,
    }));

    router.post(
        reorderColumns.url(props.projectId),
        {
            columns: payload,
        },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
}

function handleTaskListChange(
    columnId: number,
    tasks: Array<Task>,
    persist = true,
) {
    const taskIds = new Set(tasks.map((task) => task.id));

    localColumns.value = localColumns.value.map((column) => {
        if (column.id === columnId) {
            return {
                ...column,
                tasks: tasks.map((task, index) => ({
                    ...task,
                    column_id: columnId,
                    position: index + 1,
                })),
            };
        }

        return {
            ...column,
            tasks: column.tasks.filter((task) => !taskIds.has(task.id)),
        };
    });

    emit('taskListChange', columnId, tasks, persist);
}

// Modal State
const isTaskModalOpen = ref(false);
const selectedTask = ref<any>(null);
const selectedColumnId = ref<number | undefined>(undefined);

function openEditModal(task: any) {
    selectedTask.value = task;
    selectedColumnId.value = task.column_id;
    isTaskModalOpen.value = true;
}

function openAddModal(columnId: number) {
    selectedTask.value = null;
    selectedColumnId.value = columnId;
    isTaskModalOpen.value = true;
}

// Add Status State
const isAddingStatus = ref(false);
const statusForm = useForm({
    title: '',
});

function submitNewStatus() {
    statusForm.post(storeColumn.url(props.projectId), {
        onSuccess: () => {
            isAddingStatus.value = false;
            statusForm.reset();
        },
    });
}
</script>

<template>
    <div class="mx-auto max-w-6xl pb-12">
        <!-- Global Headers -->
        <div
            class="sticky top-0 z-10 mb-4 grid grid-cols-12 gap-4 border-b border-slate-700/50 bg-slate-900/90 px-4 py-3 text-xs font-semibold tracking-wider text-slate-400 uppercase backdrop-blur"
        >
            <div
                @click="toggleSort('title')"
                class="group col-span-6 flex cursor-pointer items-center transition-colors hover:text-white"
            >
                <span class="ml-7">Task Name</span>
                <svg
                    v-if="sortField === 'title'"
                    :class="{ 'rotate-180': sortDirection === 'desc' }"
                    class="ml-1 h-3 w-3 transition-transform"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 15l7-7 7 7"
                    />
                </svg>
            </div>
            <div
                @click="toggleSort('priority')"
                class="col-span-3 flex cursor-pointer items-center transition-colors hover:text-white"
            >
                <span>Priority</span>
                <svg
                    v-if="sortField === 'priority'"
                    :class="{ 'rotate-180': sortDirection === 'desc' }"
                    class="ml-1 h-3 w-3 transition-transform"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 15l7-7 7 7"
                    />
                </svg>
            </div>
            <div
                @click="toggleSort('due_date')"
                class="col-span-3 flex cursor-pointer items-center transition-colors hover:text-white"
            >
                <span>Due Date</span>
                <svg
                    v-if="sortField === 'due_date'"
                    :class="{ 'rotate-180': sortDirection === 'desc' }"
                    class="ml-1 h-3 w-3 transition-transform"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 15l7-7 7 7"
                    />
                </svg>
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
                :key="columnTaskKey(column)"
                :column="column"
                :project-id="projectId"
                :sort-field="sortField"
                :sort-direction="sortDirection"
                @edit-task="openEditModal"
                @add-task="openAddModal"
                @task-list-change="handleTaskListChange"
            />
        </VueDraggable>

        <div
            v-if="localColumns.length === 0"
            class="py-12 text-center text-slate-500"
        >
            No statuses found. Add one below to get started.
        </div>

        <!-- Add Status -->
        <div class="mt-4">
            <div
                v-if="!isAddingStatus"
                @click="isAddingStatus = true"
                class="flex cursor-pointer items-center rounded-lg border border-dashed border-slate-700 bg-slate-800/20 px-4 py-3 text-slate-500 transition-colors hover:border-slate-500 hover:bg-slate-800/40 hover:text-slate-300"
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
                <span class="font-medium">Add Status</span>
            </div>

            <div
                v-else
                class="rounded-lg border border-slate-700 bg-slate-800 p-4 shadow-sm"
            >
                <form
                    @submit.prevent="submitNewStatus"
                    class="flex items-center space-x-3"
                >
                    <input
                        v-model="statusForm.title"
                        type="text"
                        placeholder="Status name (e.g. In Review, Blocked)..."
                        class="flex-1 rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white focus:border-purple-500 focus:ring-purple-500"
                        autofocus
                    />
                    <button
                        type="submit"
                        :disabled="statusForm.processing || !statusForm.title"
                        class="rounded-md bg-purple-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-purple-500 disabled:opacity-50"
                    >
                        Save
                    </button>
                    <button
                        type="button"
                        @click="
                            isAddingStatus = false;
                            statusForm.reset();
                        "
                        class="px-2 py-2 text-sm font-medium text-slate-400 hover:text-slate-300"
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
