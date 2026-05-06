<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { logout } from '@/routes';
import ProjectCard from '@/components/ProjectCard.vue';
import ProjectFormModal from '@/components/ProjectFormModal.vue';
import DeleteModal from '@/components/DeleteModal.vue';
import { destroy } from '@/actions/App/Http/Controllers/ProjectController';
import { useDebounceFn } from '@vueuse/core';

const props = defineProps<{
    projects: Array<{
        id: number;
        name: string;
        description?: string;
        tasks_count?: number;
        updated_at: string;
    }>;
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters.search ?? '');

// Watch search input and update URL with debounce
const debouncedSearch = useDebounceFn((value) => {
    router.get(
        '/dashboard',
        { search: value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}, 300);

watch(search, debouncedSearch);

const isModalOpen = ref(false);
const selectedProject = ref<(typeof props.projects)[0] | null>(null);

function openCreateModal() {
    selectedProject.value = null;
    isModalOpen.value = true;
}

function openEditModal(project: (typeof props.projects)[0]) {
    selectedProject.value = project;
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    setTimeout(() => {
        selectedProject.value = null;
    }, 300);
}

// Delete Modal State
const isDeleteModalOpen = ref(false);
const projectToDelete = ref<(typeof props.projects)[0] | null>(null);

function openDeleteModal(project: (typeof props.projects)[0]) {
    projectToDelete.value = project;
    isDeleteModalOpen.value = true;
}

function closeDeleteModal() {
    isDeleteModalOpen.value = false;
    setTimeout(() => {
        projectToDelete.value = null;
    }, 300);
}
</script>

<template>
    <Head title="Projects" />

    <div class="flex min-h-screen flex-col bg-slate-900 text-white">
        <header
            class="sticky top-0 z-10 border-b border-slate-700 bg-slate-800 shadow"
        >
            <div
                class="mx-auto flex max-w-7xl flex-col space-y-4 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0 sm:px-6 lg:px-8"
            >
                <h2
                    class="bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-2xl leading-tight font-bold text-transparent"
                >
                    Projects
                </h2>

                <div class="flex items-center space-x-4">
                    <!-- Search bar -->
                    <div class="relative w-full sm:w-64">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                        >
                            <svg
                                class="h-5 w-5 text-slate-500"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search projects..."
                            class="block w-full rounded-md border border-slate-700 bg-slate-900 py-2 pr-3 pl-10 leading-5 text-slate-300 placeholder-slate-500 transition-colors focus:border-purple-500 focus:bg-slate-800 focus:ring-purple-500 focus:outline-none sm:text-sm"
                        />
                    </div>

                    <button
                        @click="openCreateModal"
                        class="hidden items-center justify-center rounded-md border border-transparent bg-purple-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-purple-500 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-slate-900 focus:outline-none sm:inline-flex"
                    >
                        <svg
                            class="mr-2 -ml-1 h-5 w-5"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        New Project
                    </button>

                    <Link
                        :href="logout.url()"
                        method="post"
                        as="button"
                        class="ml-4 text-sm whitespace-nowrap text-slate-400 transition hover:text-white"
                    >
                        Log Out
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <!-- Mobile create button -->
            <div class="mb-6 sm:hidden">
                <button
                    @click="openCreateModal"
                    class="flex w-full items-center justify-center rounded-md border border-transparent bg-purple-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-purple-500 focus:outline-none"
                >
                    <svg
                        class="mr-2 -ml-1 h-5 w-5"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    New Project
                </button>
            </div>

            <!-- Empty state -->
            <div
                v-if="projects.length === 0"
                class="rounded-xl border border-slate-700 bg-slate-800 py-20 text-center shadow-sm"
            >
                <svg
                    class="mx-auto h-12 w-12 text-slate-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                </svg>
                <h3 class="mt-4 text-sm font-medium text-slate-300">
                    No projects found
                </h3>
                <p class="mt-1 text-sm text-slate-500">
                    Get started by creating a new project.
                </p>
                <div class="mt-6">
                    <button
                        @click="openCreateModal"
                        type="button"
                        class="inline-flex items-center rounded-md border border-transparent bg-purple-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-purple-500 focus:outline-none"
                    >
                        <svg
                            class="mr-2 -ml-1 h-5 w-5"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        New Project
                    </button>
                </div>
            </div>

            <!-- Projects Grid -->
            <div
                v-else
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                <ProjectCard
                    v-for="project in projects"
                    :key="project.id"
                    :project="project"
                    @edit="openEditModal"
                    @delete="openDeleteModal"
                />
            </div>
        </main>

        <!-- Modals -->
        <ProjectFormModal
            :show="isModalOpen"
            :project="selectedProject"
            @close="closeModal"
        />

        <DeleteModal
            :show="isDeleteModalOpen"
            :url="projectToDelete ? destroy.url(projectToDelete.id) : null"
            title="Delete Project"
            :message="`Are you sure you want to delete ${projectToDelete?.name}? All tasks and columns associated with this project will also be deleted.`"
            @close="closeDeleteModal"
        />
    </div>
</template>
