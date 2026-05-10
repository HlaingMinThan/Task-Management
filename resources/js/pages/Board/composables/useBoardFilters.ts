import { ref } from 'vue'

const search = ref('')
const status = ref('')
const assigned = ref('')
const dueDate = ref('')
const overdue = ref(false)
const selectedTaskId = ref<number | null>(null)

function selectTask(id: number | null) {
    selectedTaskId.value = id
}

export function useBoardFilters() {
    function clear() {
        search.value = ''
        status.value = ''
        assigned.value = ''
    dueDate.value = ''
        overdue.value = false
        selectedTaskId.value = null
    }

    return {
        search,
        status,
        assigned,
    dueDate,
        overdue,
        selectedTaskId,
        selectTask,
        clear,
    }
}

export default useBoardFilters
