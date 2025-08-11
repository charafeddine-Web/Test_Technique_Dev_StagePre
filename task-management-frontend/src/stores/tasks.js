import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useTasksStore = defineStore('tasks', () => {
  // State
  const tasks = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Getters
  const getTasks = computed(() => tasks.value)
  const getLoading = computed(() => loading.value)
  const getError = computed(() => error.value)
  const getTasksByStatus = computed(() => (status) => {
    return tasks.value.filter(task => task.status === status)
  })

  // Actions
  const fetchTasks = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get('http://localhost:8000/api/tasks')
      
      if (response.data.success) {
        tasks.value = response.data.data.tasks
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement des tâches'
      console.error('Erreur fetchTasks:', err)
    } finally {
      loading.value = false
    }
  }

  const createTask = async (taskData) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.post('http://localhost:8000/api/tasks', taskData)
      
      if (response.data.success) {
        tasks.value.unshift(response.data.data.task)
        return { success: true, data: response.data.data.task }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la création de la tâche'
      console.error('Erreur createTask:', err)
      return { 
        success: false, 
        message: error.value,
        errors: err.response?.data?.errors || {}
      }
    } finally {
      loading.value = false
    }
  }

  const updateTask = async (taskId, taskData) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.put(`http://localhost:8000/api/tasks/${taskId}`, taskData)
      
      if (response.data.success) {
        const updatedTask = response.data.data.task
        const index = tasks.value.findIndex(task => task.id === taskId)
        
        if (index !== -1) {
          tasks.value[index] = updatedTask
        }
        
        return { success: true, data: updatedTask }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la modification de la tâche'
      console.error('Erreur updateTask:', err)
      return { 
        success: false, 
        message: error.value,
        errors: err.response?.data?.errors || {}
      }
    } finally {
      loading.value = false
    }
  }

  const deleteTask = async (taskId) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.delete(`http://localhost:8000/api/tasks/${taskId}`)
      
      if (response.data.success) {
        tasks.value = tasks.value.filter(task => task.id !== taskId)
        return { success: true }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la suppression de la tâche'
      console.error('Erreur deleteTask:', err)
      return { success: false, message: error.value }
    } finally {
      loading.value = false
    }
  }

  const getTask = async (taskId) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get(`http://localhost:8000/api/tasks/${taskId}`)
      
      if (response.data.success) {
        return { success: true, data: response.data.data.task }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement de la tâche'
      console.error('Erreur getTask:', err)
      return { success: false, message: error.value }
    } finally {
      loading.value = false
    }
  }

  const addTaskFromNotification = (task) => {
    // Ajouter une tâche reçue via notification en temps réel
    const existingTask = tasks.value.find(t => t.id === task.id)
    if (!existingTask) {
      tasks.value.unshift(task)
    }
  }

  const clearError = () => {
    error.value = null
  }

  return {
    // State
    tasks,
    loading,
    error,
    
    // Getters
    getTasks,
    getLoading,
    getError,
    getTasksByStatus,
    
    // Actions
    fetchTasks,
    createTask,
    updateTask,
    deleteTask,
    getTask,
    addTaskFromNotification,
    clearError
  }
})
