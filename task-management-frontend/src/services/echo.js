import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { useAuthStore } from '@/stores/auth'
import { useTasksStore } from '@/stores/tasks'
import { useNotificationsStore } from '@/stores/notifications'

let echo = null

export const initializeEcho = () => {
  const authStore = useAuthStore()
  const token = authStore.getToken

  if (!token) {
    console.warn('Aucun token disponible pour initialiser Echo')
    return null
  }

  // Configuration Pusher (à adapter selon votre configuration)
  window.Pusher = Pusher

  echo = new Echo({
    broadcaster: 'pusher',
    key: 'your-pusher-key', // Remplacez par votre clé Pusher
    cluster: 'your-cluster', // Remplacez par votre cluster
    forceTLS: true,
    authEndpoint: 'http://localhost:8000/api/broadcasting/auth',
    auth: {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      }
    }
  })

  return echo
}

export const setupEchoListeners = () => {
  if (!echo) {
    console.warn('Echo n\'est pas initialisé')
    return
  }

  const authStore = useAuthStore()
  const tasksStore = useTasksStore()
  const notificationsStore = useNotificationsStore()

  const user = authStore.getUser
  if (!user) {
    console.warn('Aucun utilisateur connecté')
    return
  }

  // S'abonner au canal privé de l'utilisateur
  const channel = echo.private(`user.${user.id}`)

  // Écouter l'event de création de tâche
  channel.listen('task.created', (data) => {
    console.log('Nouvelle tâche créée reçue:', data)
    
    // Ajouter la tâche au store
    tasksStore.addTaskFromNotification(data.task)
    
    // Ajouter la notification au store
    notificationsStore.addTaskCreatedNotification(data)
    
    // Afficher une notification toast (optionnel)
    showToastNotification(data.message, 'success')
  })

  // Écouter les notifications Laravel
  channel.listen('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', (data) => {
    console.log('Nouvelle notification reçue:', data)
    
    // Ajouter la notification au store
    notificationsStore.addNotification(data)
    
    // Afficher une notification toast (optionnel)
    showToastNotification(data.message || 'Nouvelle notification', 'info')
  })

  console.log('Écouteurs Echo configurés pour l\'utilisateur:', user.id)
}

export const disconnectEcho = () => {
  if (echo) {
    echo.disconnect()
    echo = null
    console.log('Echo déconnecté')
  }
}

export const getEcho = () => {
  return echo
}

// Fonction utilitaire pour afficher des notifications toast
const showToastNotification = (message, type = 'info') => {
  // Vous pouvez implémenter votre propre système de toast ici
  // Par exemple avec vue-toastification ou un composant personnalisé
  console.log(`[${type.toUpperCase()}] ${message}`)
  
  // Exemple simple avec alert (à remplacer par votre système de toast)
  // alert(message)
}

export default {
  initializeEcho,
  setupEchoListeners,
  disconnectEcho,
  getEcho
}
