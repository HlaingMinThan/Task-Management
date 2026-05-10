<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { VueDraggable } from 'vue-draggable-plus';
import {
    update,
    destroy,
} from '@/actions/App/Http/Controllers/ColumnController';
import { reorder as reorderTasks } from '@/actions/App/Http/Controllers/TaskController';
import DeleteModal from '@/components/DeleteModal.vue';
import { useBoardFilters } from '../composables/useBoardFilters';
import TaskCard from './TaskCard.vue';
import TaskFormModal from './TaskFormModal.vue';

const props = defineProps<{
    projectId: number;
    column: {
        id: number;
        title: string;
        position: number;
        tasks: Array<{
            id: number;
            title: string;
            description?: string;
            priority: 'low' | 'medium' | 'high';
            due_date?: string;
            position: number;
        }>;
    };
}>();

const tasks = ref([...(props.column.tasks ?? [])]);

const {
    search,
    status: filterStatus,
    assigned,
    dueDate,
    overdue,
    selectedTaskId,
} = useBoardFilters();

const filteredTasks = computed(() => {
    const q = (search.value || '').toLowerCase().trim();
    const now = new Date();
    const startOfToday = new Date(now.setHours(0, 0, 0, 0));
    const endOfToday = new Date(startOfToday);
    endOfToday.setHours(23, 59, 59, 999);

    const target = dueDate.value ? new Date(dueDate.value) : null;
    let targetEnd: Date | null = null;
    if (target) {
        target.setHours(0, 0, 0, 0);
        targetEnd = new Date(target);
        targetEnd.setHours(23, 59, 59, 999);
    }

    return tasks.value.filter((task: any) => {
        // Only tasks that belong to this column are already in tasks list

        // Status filter: column id match (status is column id)
        if (filterStatus.value) {
            // if the selected status (column id) doesn't match this column, hide all tasks
            if (Number(filterStatus.value) !== props.column.id) {
return false;
}
        }

        // Search filter: check title and description
        if (q) {
            const inTitle = (task.title || '').toLowerCase().includes(q);
            const inDesc = (task.description || '').toLowerCase().includes(q);

            if (!inTitle && !inDesc) {
return false;
}
        }

        // Assigned filter
        if (assigned.value) {
            if (assigned.value === 'me') {
                // If task.assigned_user_id exists, compare to 'me' placeholder not resolvable client-side; skip if not present
                // For now, only show tasks with assigned_user_id === 'me' string
                if (String(task.assigned_user_id || '') !== 'me') {
return false;
}
            } else {
                if (
                    String(task.assigned_user_id || '') !==
                    String(assigned.value)
                ) {
return false;
}
            }
        }

        // Due date filter (range)
        if (target) {
            if (!task.due_date) return false;
            const dueDate = new Date(task.due_date);
            if (dueDate < target || (targetEnd && dueDate > targetEnd)) return false;
        }

        // Overdue toggle
        if (overdue.value) {
            if (!task.due_date) {
return false;
}

            const dueDate = new Date(task.due_date);
            const isOver = dueDate < new Date(new Date().setHours(0, 0, 0, 0));

            if (!isOver) {
return false;
}
        }

        return true;
    });
});

// scroll to and highlight selected task if it belongs to this column
watch(
    () => selectedTaskId.value,
    (id) => {
        if (!id) {
return;
}

        const el = document.getElementById(`task-${id}`);

        if (!el) {
return;
}

        // ensure the element is visible within this column's scrollable area
        // find the nearest scrollable ancestor (the column body)
        let parent: HTMLElement | null = el.parentElement;

        while (parent && !parent.classList.contains('overflow-y-auto')) {
            parent = parent.parentElement;
        }

        if (parent) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    },
);

watch(
    () => props.column.tasks,
    (newTasks) => {
        tasks.value = [...(newTasks ?? [])];
    },
    { deep: true },
);

const isEditing = ref(false);
const inputRef = ref<HTMLInputElement | null>(null);

const form = useForm({
    title: props.column.title,
});

function startEditing() {
    isEditing.value = true;
    form.title = props.column.title;
    setTimeout(() => {
        inputRef.value?.focus();
        inputRef.value?.select();
    }, 50);
}

function saveEdit() {
    if (!form.title || form.title === props.column.title) {
        isEditing.value = false;
        form.title = props.column.title;

        return;
    }

    form.patch(update.url([props.projectId, props.column.id]), {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
    });
}

const isDeleteModalOpen = ref(false);

function confirmDelete() {
    isDeleteModalOpen.value = true;
}

const isTaskModalOpen = ref(false);
const selectedTask = ref<any>(null);

function addTask() {
    selectedTask.value = null;
    isTaskModalOpen.value = true;
}

function editTask(task: any) {
    selectedTask.value = task;
    isTaskModalOpen.value = true;
}

function handleTaskReorder() {
    const payload = tasks.value.map((task, index) => ({
        id: task.id,
        position: index + 1,
    }));

    router.post(
        reorderTasks.url(props.projectId),
        {
            column_id: props.column.id,
            tasks: payload,
        },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
}
</script>

<template>
    <div
        class="flex max-h-full w-80 shrink-0 flex-col rounded-lg border border-slate-700 bg-slate-800 shadow-sm"
    >
        <!-- Column Header -->
        <div
            class="group flex items-start justify-between border-b border-slate-700 p-3"
        >
            <div class="mr-2 flex min-w-0 flex-1 items-center">
                <!-- Drag Handle -->
                <div
                    class="column-handle mr-2 shrink-0 cursor-grab text-slate-500 hover:text-slate-300 active:cursor-grabbing"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
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

                <div class="min-w-0 flex-1">
                    <form v-if="isEditing" @submit.prevent="saveEdit">
                        <input
                            ref="inputRef"
                            v-model="form.title"
                            type="text"
                            @blur="saveEdit"
                            @keyup.escape="isEditing = false"
                            class="w-full rounded border-purple-500 bg-slate-900 px-2 py-1 text-sm font-semibold text-white focus:border-purple-500 focus:ring-purple-500"
                        />
                    </form>
                    <div
                        v-else
                        @click="startEditing"
                        class="cursor-text truncate rounded px-2 py-1 text-sm font-semibold text-white transition hover:bg-slate-700"
                    >
                        {{ column.title }}
                    </div>
                </div>
            </div>

            <button
                @click="confirmDelete"
                class="rounded p-1 text-slate-500 opacity-0 transition-opacity group-hover:opacity-100 hover:bg-slate-700 hover:text-red-400"
                title="Delete Column"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>
        </div>

        <!-- Column Body -->
        <div class="min-h-[100px] flex-1 overflow-y-auto p-2">
            <VueDraggable
                v-model="tasks"
                group="tasks"
                @change="handleTaskReorder"
                :animation="150"
                class="min-h-[50px]"
            >
                <TaskCard
                    v-for="task in filteredTasks"
                    :key="task.id"
                    :task="task"
                    :highlight="selectedTaskId === task.id"
                    @click="editTask(task)"
                />
            </VueDraggable>

            <div
                v-if="tasks.length === 0"
                class="mt-4 text-center text-xs text-slate-500"
            >
                No tasks yet
            </div>
        </div>

        <!-- Add Task Button -->
        <div class="border-t border-slate-700 p-2">
            <button
                @click="addTask"
                class="flex w-full items-center justify-center rounded px-4 py-2 text-sm text-slate-400 transition-colors hover:bg-slate-700 hover:text-white"
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
                Add Task
            </button>
        </div>

        <DeleteModal
            :show="isDeleteModalOpen"
            :url="destroy.url([projectId, column.id])"
            title="Delete Column"
            :message="`Are you sure you want to delete '${column.title}'? This will also delete all tasks in this column.`"
            @close="isDeleteModalOpen = false"
        />

        <TaskFormModal
            :show="isTaskModalOpen"
            :project-id="projectId"
            :column-id="column.id"
            :task="selectedTask"
            @close="isTaskModalOpen = false"
        />
    </div>
</template>
