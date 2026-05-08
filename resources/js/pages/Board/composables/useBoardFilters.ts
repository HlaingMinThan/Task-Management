import { ref, computed } from 'vue'

const search = ref('')
const status = ref('')
const assigned = ref('')
const due = ref('') // '', 'today', 'week'
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
        due.value = ''
        overdue.value = false
        selectedTaskId.value = null
    }

    return {
        search,
        status,
        assigned,
        due,
        overdue,
        selectedTaskId,
        selectTask,
        clear,
    }
}

export default useBoardFilters
