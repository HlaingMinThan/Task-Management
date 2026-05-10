<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    task: {
        id: number;
        title: string;
        description?: string;
        priority: 'low' | 'medium' | 'high';
        due_date?: string;
    };
    highlight?: boolean;
}>();

const priorityClass = computed(() => {
    switch (props.task.priority) {
        case 'high':
            return 'bg-red-500/10 text-red-400 border-red-500/20';
        case 'medium':
            return 'bg-orange-500/10 text-orange-400 border-orange-500/20';
        case 'low':
            return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
        default:
            return 'bg-slate-500/10 text-slate-400 border-slate-500/20';
    }
});

const formattedDate = computed(() => {
    if (!props.task.due_date) {
return null;
}

    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
    }).format(new Date(props.task.due_date));
});

const isOverdue = computed(() => {
    if (!props.task.due_date) {
return false;
}

    return (
        new Date(props.task.due_date) <
        new Date(new Date().setHours(0, 0, 0, 0))
    );
});
</script>

<template>
    <div
        :id="`task-${task.id}`"
        :class="[
            'group mb-2 cursor-pointer rounded-lg border border-slate-600 bg-slate-700/50 p-3 shadow-sm transition-all hover:bg-slate-700',
            highlight ? 'ring-2 ring-purple-500' : '',
        ]"
    >
        <div class="mb-2 flex items-start justify-between">
            <span
                :class="[
                    'rounded border px-1.5 py-0.5 text-[10px] font-bold tracking-wider uppercase',
                    priorityClass,
                ]"
            >
                {{ task.priority }}
            </span>
        </div>

        <h4 class="mb-2 text-sm leading-tight font-medium text-white">
            {{ task.title }}
        </h4>

        <div
            v-if="formattedDate"
            class="flex items-center text-[11px]"
            :class="isOverdue ? 'text-red-400' : 'text-slate-400'"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="mr-1 h-3 w-3"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path
                    fill-rule="evenodd"
                    d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                    clip-rule="evenodd"
                />
            </svg>
            {{ formattedDate }}
        </div>
    </div>
</template>
