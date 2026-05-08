<script setup lang="ts">
import { onMounted, onBeforeUnmount, watch } from 'vue';
import { useBoardFilters } from '../composables/useBoardFilters';

const props = defineProps({
    show: { type: Boolean, required: true },
    project: { type: Object as any, required: true },
});

const emit = defineEmits<{ (e: 'update:show', value: boolean): void }>();

const {
    search,
    status,
    assigned,
    due,
    overdue,
    clear,
    selectTask,
    selectedTaskId,
} = useBoardFilters();

function close() {
    emit('update:show', false);
}

function onKey(e: KeyboardEvent) {
    if (e.key === 'Escape') close();
}

onMounted(() => {
    window.addEventListener('keydown', onKey);
});
onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKey);
});

import { ref } from 'vue';
const inputRef = ref<HTMLInputElement | null>(null);

watch(
    () => props.show,
    (val) => {
        if (val) {
            // give the modal a tick to render then focus
            setTimeout(() => inputRef.value?.focus(), 50);
        }
    },
);

// compute results across all columns/tasks in the project using same filters as columns
import { computed } from 'vue';

const results = computed(() => {
    const q = (search.value || '').toLowerCase().trim();
    const now = new Date();
    const startOfToday = new Date(now.setHours(0, 0, 0, 0));
    const endOfToday = new Date(startOfToday);
    endOfToday.setHours(23, 59, 59, 999);

    const startOfWeek = new Date(startOfToday);
    startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay());
    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(startOfWeek.getDate() + 6);
    endOfWeek.setHours(23, 59, 59, 999);

    const cols = props.project.columns || [];
    let allTasks: Array<any> = [];
    cols.forEach((c: any) => {
        (c.tasks || []).forEach((t: any) =>
            allTasks.push({ ...t, columnId: c.id, columnTitle: c.title }),
        );
    });

    return allTasks.filter((task: any) => {
        // status filter: match column id if set
        if (status.value && Number(status.value) !== task.columnId)
            return false;

        if (q) {
            const inTitle = (task.title || '').toLowerCase().includes(q);
            const inDesc = (task.description || '').toLowerCase().includes(q);
            if (!inTitle && !inDesc) return false;
        }

        if (assigned.value) {
            if (assigned.value === 'me') {
                if (String(task.assigned_user_id || '') !== 'me') return false;
            } else {
                if (
                    String(task.assigned_user_id || '') !==
                    String(assigned.value)
                )
                    return false;
            }
        }

        if (due.value) {
            if (!task.due_date) return false;
            const dueDate = new Date(task.due_date);
            if (due.value === 'today') {
                if (dueDate < startOfToday || dueDate > endOfToday)
                    return false;
            } else if (due.value === 'week') {
                if (dueDate < startOfWeek || dueDate > endOfWeek) return false;
            }
        }

        if (overdue.value) {
            if (!task.due_date) return false;
            const dueDate = new Date(task.due_date);
            if (!(dueDate < new Date(new Date().setHours(0, 0, 0, 0))))
                return false;
        }

        return true;
    });
});

function clickResult(task: any) {
    selectTask(task.id);
    // emit close
    close();
}
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50">
        <!-- Backdrop -->
        <div
            class="absolute inset-0 bg-black/40 backdrop-blur-sm"
            @click="close"
        ></div>

        <!-- Top-middle modal -->
        <div
            class="absolute top-4 left-1/2 z-50 w-full max-w-3xl -translate-x-1/2 transform"
        >
            <div
                class="rounded-lg border border-slate-700 bg-slate-800 p-4 shadow-lg"
            >
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white">
                        Search Results
                    </h3>
                    <button
                        @click="close"
                        class="text-slate-400 hover:text-white"
                    >
                        ✕
                    </button>
                </div>

                <div class="mb-2">
                    <input
                        ref="inputRef"
                        v-model="search"
                        type="text"
                        placeholder="Search title or description..."
                        class="w-full rounded border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white"
                    />
                </div>

                <div class="mb-3 flex items-center space-x-3">
                    <select
                        v-model="status"
                        class="rounded border border-slate-700 bg-slate-900 px-2 py-2 text-sm text-white"
                    >
                        <option value="">All statuses</option>
                        <option
                            v-for="col in project.columns"
                            :key="col.id"
                            :value="col.id"
                        >
                            {{ col.title }}
                        </option>
                    </select>

                    <select
                        v-model="assigned"
                        class="rounded border border-slate-700 bg-slate-900 px-2 py-2 text-sm text-white"
                    >
                        <option value="">All users</option>
                        <option value="me">Me</option>
                    </select>

                    <select
                        v-model="due"
                        class="rounded border border-slate-700 bg-slate-900 px-2 py-2 text-sm text-white"
                    >
                        <option value="">Any due date</option>
                        <option value="today">Today</option>
                        <option value="week">This week</option>
                    </select>

                    <label
                        class="inline-flex items-center space-x-2 text-sm text-slate-300"
                    >
                        <input
                            type="checkbox"
                            v-model="overdue"
                            class="rounded bg-slate-900"
                        />
                        <span>Overdue</span>
                    </label>

                    <div class="ml-auto">
                        <button
                            @click="clear()"
                            class="rounded bg-slate-700 px-3 py-1 text-sm text-white hover:bg-slate-600"
                        >
                            Clear All
                        </button>
                    </div>
                </div>

                <div class="max-h-64 overflow-auto">
                    <div
                        v-if="results.length === 0"
                        class="text-sm text-slate-400"
                    >
                        No results
                    </div>
                    <ul>
                        <li
                            v-for="r in results"
                            :key="r.id"
                            class="border-b border-slate-700 py-2"
                        >
                            <button
                                @click="clickResult(r)"
                                class="w-full text-left"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div
                                            class="text-sm font-medium text-white"
                                        >
                                            {{ r.title }}
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            {{ r.columnTitle }} ·
                                            {{ r.description }}
                                        </div>
                                    </div>
                                    <div class="text-xs text-slate-300">
                                        View
                                    </div>
                                </div>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
