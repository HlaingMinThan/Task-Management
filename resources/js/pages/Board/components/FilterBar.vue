<script setup lang="ts">
import { ref } from 'vue';
import SearchFilterModal from './SearchFilterModal.vue';
import { usePage } from '@inertiajs/vue3';
import { useBoardFilters } from '../composables/useBoardFilters';

const page = usePage() as any;
const { search, status, assigned, due, overdue, clear, selectTask } =
    useBoardFilters();

const showModal = ref(false);

function onSearchFocus() {
    // show results modal when user focuses search
    showModal.value = true;
}

function clearAll() {
    clear();
    // also clear any selection
    selectTask(null);
}
</script>

<template>
    <div class="w-full max-w-full">
        <div class="flex items-center space-x-3">
            <input
                v-model="search"
                @focus="onSearchFocus"
                @click="onSearchFocus"
                type="text"
                placeholder="Search tasks (click to open filters)..."
                class="w-72 cursor-text rounded border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white"
            />

            <!-- Modal triggered on focus/click; visible filters are inside the modal -->
            <SearchFilterModal
                :show="showModal"
                :project="page.props.project"
                @update:show="showModal = $event"
            />
        </div>
    </div>
</template>
