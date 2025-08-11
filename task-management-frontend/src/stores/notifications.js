import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useNotificationsStore = defineStore('notifications', () => {
  // State
  const notifications = ref([])
  const unreadCount = ref(0)
  const loading = ref(false)
  const error = ref(null)

  // Getters
  const getNotifications = computed(() => notifications.value)
  const getUnreadCount = computed(() => unreadCount.value)
  const getLoading = computed(() => loading.value)
  const getError = computed(() => error.value)
  const getUnreadNotifications = computed(() => {
    return notifications.value.filter(notification => !notification.read_at)
  })

  // Actions
  const fetchNotifications = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get('http://localhost:8000/api/notifications')
      
      if (response.data.success) {
        notifications.value = response.data.data.notifications
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement des notifications'
      console.error('Erreur fetchNotifications:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchUnreadCount = async () => {
    try {
      const response = await axios.get('http://localhost:8000/api/notifications/unread-count')
      
      if (response.data.success) {
        unreadCount.value = response.data.data.unread_count
      }
    } catch (err) {
      console.error('Erreur fetchUnreadCount:', err)
    }
  }

  const markAsRead = async (notificationId) => {
    try {
      const response = await axios.patch(`http://localhost:8000/api/notifications/${notificationId}/read`)
      
      if (response.data.success) {
        const notification = notifications.value.find(n => n.id === notificationId)
        if (notification) {
          notification.read_at = new Date().toISOString()
        }
        
        // Mettre à jour le compteur
        await fetchUnreadCount()
      }
    } catch (err) {
      console.error('Erreur markAsRead:', err)
    }
  }

  const markAllAsRead = async () => {
    try {
      const response = await axios.patch('http://localhost:8000/api/notifications/mark-all-read')
      
      if (response.data.success) {
        notifications.value.forEach(notification => {
          notification.read_at = new Date().toISOString()
        })
        
        unreadCount.value = 0
      }
    } catch (err) {
      console.error('Erreur markAllAsRead:', err)
    }
  }

  const deleteNotification = async (notificationId) => {
    try {
      const response = await axios.delete(`http://localhost:8000/api/notifications/${notificationId}`)
      
      if (response.data.success) {
        notifications.value = notifications.value.filter(n => n.id !== notificationId)
        
        // Mettre à jour le compteur
        await fetchUnreadCount()
      }
    } catch (err) {
      console.error('Erreur deleteNotification:', err)
    }
  }

  const addNotification = (notification) => {
    // Ajouter une notification reçue en temps réel
    notifications.value.unshift(notification)
    unreadCount.value++
  }

  const addTaskCreatedNotification = (data) => {
    // Créer une notification à partir de l'event TaskCreated
    const notification = {
      id: `temp-${Date.now()}`,
      type: 'task_created',
      data: {
        task_id: data.task.id,
        task_title: data.task.title,
        task_description: data.task.description,
        task_status: data.task.status,
        message: data.message,
        type: 'task_created',
        created_at: data.timestamp
      },
      read_at: null,
      created_at: data.timestamp,
      updated_at: data.timestamp
    }
    
    addNotification(notification)
  }

  const clearError = () => {
    error.value = null
  }

  return {
    // State
    notifications,
    unreadCount,
    loading,
    error,
    
    // Getters
    getNotifications,
    getUnreadCount,
    getLoading,
    getError,
    getUnreadNotifications,
    
    // Actions
    fetchNotifications,
    fetchUnreadCount,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    addNotification,
    addTaskCreatedNotification,
    clearError
  }
})
