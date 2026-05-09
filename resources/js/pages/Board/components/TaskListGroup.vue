<script setup lang="ts">
import { ref, watch } from 'vue';
import { VueDraggable } from 'vue-draggable-plus';
import TaskListItem from './TaskListItem.vue';

type Task = {
    id: number;
    column_id?: number;
    title: string;
    description?: string;
    priority: 'low' | 'medium' | 'high';
    due_date?: string;
    position: number;
};

const props = defineProps<{
    projectId: number;
    column: {
        id: number;
        title: string;
        position: number;
        tasks: Array<Task>;
    };
    sortField: 'none' | 'title' | 'priority' | 'due_date';
    sortDirection: 'asc' | 'desc';
}>();

const emit = defineEmits<{
    (e: 'editTask', task: Task): void;
    (e: 'addTask', columnId: number): void;
    (
        e: 'taskListChange',
        columnId: number,
        tasks: Array<Task>,
        persist?: boolean,
    ): void;
}>();

function sortedTasks(tasks: Array<Task>) {
    if (props.sortField === 'none') {
        return [...tasks];
    }

    return [...tasks].sort((a, b) => {
        let valA: string | number | undefined = a[props.sortField as 'title' | 'priority' | 'due_date'];
        let valB: string | number | undefined = b[props.sortField as 'title' | 'priority' | 'due_date'];

        if (props.sortField === 'priority') {
            const weight = { high: 3, medium: 2, low: 1 };
            valA = weight[a.priority];
            valB = weight[b.priority];
        }

        valA ??= '';
        valB ??= '';

        if (valA < valB) {
            return props.sortDirection === 'asc' ? -1 : 1;
        }

        if (valA > valB) {
            return props.sortDirection === 'asc' ? 1 : -1;
        }

        return 0;
    });
}

const localTasks = ref(sortedTasks(props.column.tasks ?? []));

watch(
    () => props.column.tasks,
    (newTasks) => {
        localTasks.value = sortedTasks(newTasks ?? []);
    },
    { deep: true },
);

watch([() => props.sortField, () => props.sortDirection], () => {
    localTasks.value = sortedTasks(props.column.tasks ?? []);
});

function handleTaskListChange(persist = true) {
    emit('taskListChange', props.column.id, localTasks.value, persist);
}

const isCollapsed = ref(false);
</script>

<template>
    <div
        class="mb-6 overflow-hidden rounded-lg border border-slate-700/50 bg-slate-800/30"
    >
        <!-- Group Header -->
        <div
            class="group/header flex items-center border-b border-slate-700/50 bg-slate-900/50 px-4 py-3"
        >
            <div
                class="column-handle mr-2 shrink-0 cursor-grab text-slate-500 hover:text-slate-300 active:cursor-grabbing"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    />
                </svg>
            </div>

            <button
                @click="isCollapsed = !isCollapsed"
                class="mr-2 text-slate-400 transition-colors hover:text-white"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 transition-transform"
                    :class="{ '-rotate-90': isCollapsed }"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>

            <h3 class="mr-3 text-base font-bold text-white">
                {{ column.title }}
            </h3>
            <span
                class="rounded-full bg-slate-800 px-2 py-0.5 text-xs font-medium text-slate-500"
                >{{ localTasks.length }}</span
            >
        </div>

        <!-- Group Body (Tasks) -->
        <div v-show="!isCollapsed">
            <VueDraggable
                v-model="localTasks"
                group="tasks"
                @add="handleTaskListChange()"
                @remove="handleTaskListChange(localTasks.length > 0)"
                @update="handleTaskListChange()"
                handle=".task-handle"
                :animation="150"
                class="min-h-[10px]"
                :disabled="sortField !== 'none'"
            >
                <TaskListItem
                    v-for="task in localTasks"
                    :key="task.id"
                    :task="task"
                    :is-sort-active="sortField !== 'none'"
                    @click="emit('editTask', task)"
                />
            </VueDraggable>

            <!-- Add Task Button for this group -->
            <div
                class="border-t border-slate-700/50 bg-slate-800 px-4 py-2 transition-colors hover:bg-slate-700/50"
            >
                <button
                    @click="emit('addTask', column.id)"
                    class="flex items-center py-1 text-sm text-slate-400 transition-colors hover:text-white"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="mr-1.5 h-4 w-4"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    New Task
                </button>
            </div>
        </div>
    </div>
</template>
