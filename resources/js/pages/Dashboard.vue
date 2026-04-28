<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { logout } from '@/routes'
import ProjectCard from '@/Components/ProjectCard.vue'
import ProjectFormModal from '@/Components/ProjectFormModal.vue'
import DeleteModal from '@/Components/DeleteModal.vue'
import { destroy } from '@/actions/App/Http/Controllers/ProjectController'
import { useDebounceFn } from '@vueuse/core'

const props = defineProps<{
    projects: Array<{
        id: number
        name: string
        description?: string
        tasks_count?: number
        updated_at: string
    }>
    filters: {
        search?: string
    }
}>()

const search = ref(props.filters.search ?? '')

// Watch search input and update URL with debounce
const debouncedSearch = useDebounceFn((value) => {
    router.get('/dashboard', { search: value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}, 300)

watch(search, debouncedSearch)

const isModalOpen = ref(false)
const selectedProject = ref<typeof props.projects[0] | null>(null)

function openCreateModal() {
    selectedProject.value = null
    isModalOpen.value = true
}

function openEditModal(project: typeof props.projects[0]) {
    selectedProject.value = project
    isModalOpen.value = true
}

function closeModal() {
    isModalOpen.value = false
    setTimeout(() => {
        selectedProject.value = null
    }, 300)
}

// Delete Modal State
const isDeleteModalOpen = ref(false)
const projectToDelete = ref<typeof props.projects[0] | null>(null)

function openDeleteModal(project: typeof props.projects[0]) {
    projectToDelete.value = project
    isDeleteModalOpen.value = true
}

function closeDeleteModal() {
    isDeleteModalOpen.value = false
    setTimeout(() => {
        projectToDelete.value = null
    }, 300)
}
</script>

<template>
  <Head title="Projects" />

  <div class="min-h-screen bg-slate-900 text-white flex flex-col">
    <header class="bg-slate-800 shadow sticky top-0 z-10 border-b border-slate-700">
      <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
        <h2 class="font-bold text-2xl leading-tight text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-500">
          Projects
        </h2>
        
        <div class="flex items-center space-x-4">
            <!-- Search bar -->
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input 
                    v-model="search"
                    type="text" 
                    placeholder="Search projects..." 
                    class="block w-full pl-10 pr-3 py-2 border border-slate-700 rounded-md leading-5 bg-slate-900 text-slate-300 placeholder-slate-500 focus:outline-none focus:bg-slate-800 focus:border-purple-500 focus:ring-purple-500 sm:text-sm transition-colors"
                >
            </div>
            
            <button @click="openCreateModal" class="hidden sm:inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 focus:ring-offset-slate-900 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                New Project
            </button>
            
            <Link :href="logout.url()" method="post" as="button" class="text-sm text-slate-400 hover:text-white transition whitespace-nowrap ml-4">
              Log Out
            </Link>
        </div>
      </div>
    </header>

    <main class="flex-1 max-w-7xl w-full mx-auto py-8 px-4 sm:px-6 lg:px-8">
      
      <!-- Mobile create button -->
      <div class="mb-6 sm:hidden">
          <button @click="openCreateModal" class="w-full flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-500 focus:outline-none transition-colors">
              <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
              </svg>
              New Project
          </button>
      </div>

      <!-- Empty state -->
      <div v-if="projects.length === 0" class="text-center py-20 bg-slate-800 rounded-xl border border-slate-700 shadow-sm">
        <svg class="mx-auto h-12 w-12 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-4 text-sm font-medium text-slate-300">No projects found</h3>
        <p class="mt-1 text-sm text-slate-500">Get started by creating a new project.</p>
        <div class="mt-6">
          <button @click="openCreateModal" type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-500 focus:outline-none transition-colors">
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            New Project
          </button>
        </div>
      </div>

      <!-- Projects Grid -->
      <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
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
