<template>
  <div class="notifications-container">
    <div class="notifications-header">
      <h1>Notifications</h1>
      <div class="header-actions">
        <button 
          @click="markAllAsRead" 
          :disabled="notificationsStore.unreadCount === 0"
          class="btn-secondary"
        >
          Marquer tout comme lu
        </button>
      </div>
    </div>

    <!-- Statistiques -->
    <div class="notifications-stats">
      <div class="stat-item">
        <span class="stat-number">{{ notificationsStore.notifications.length }}</span>
        <span class="stat-label">Total</span>
      </div>
      <div class="stat-item">
        <span class="stat-number unread">{{ notificationsStore.unreadCount }}</span>
        <span class="stat-label">Non lues</span>
      </div>
    </div>

    <!-- Liste des notifications -->
    <div class="notifications-content">
      <div v-if="notificationsStore.loading" class="loading">
        Chargement des notifications...
      </div>

      <div v-else-if="notificationsStore.notifications.length === 0" class="empty-state">
        <div class="empty-icon">🔔</div>
        <h3>Aucune notification</h3>
        <p>Vous n'avez pas encore reçu de notifications</p>
      </div>

      <div v-else class="notifications-list">
        <div
          v-for="notification in notificationsStore.notifications"
          :key="notification.id"
          class="notification-item"
          :class="{ 'unread': !notification.read_at }"
        >
          <div class="notification-icon">
            <span v-if="notification.data.type === 'task_created'">📝</span>
            <span v-else>🔔</span>
          </div>

          <div class="notification-content">
            <div class="notification-header">
              <h4>{{ notification.data.message }}</h4>
              <div class="notification-actions">
                <button 
                  v-if="!notification.read_at"
                  @click="markAsRead(notification.id)"
                  class="action-btn"
                  title="Marquer comme lue"
                >
                  ✓
                </button>
                <button 
                  @click="deleteNotification(notification.id)"
                  class="action-btn delete"
                  title="Supprimer"
                >
                  ×
                </button>
              </div>
            </div>

            <div v-if="notification.data.task_title" class="notification-details">
              <div class="task-info">
                <strong>Tâche:</strong> {{ notification.data.task_title }}
              </div>
              <div v-if="notification.data.task_description" class="task-info">
                <strong>Description:</strong> {{ notification.data.task_description }}
              </div>
              <div class="task-info">
                <strong>Statut:</strong> 
                <span class="task-status" :class="getStatusClass(notification.data.task_status)">
                  {{ getStatusLabel(notification.data.task_status) }}
                </span>
              </div>
            </div>

            <div class="notification-footer">
              <span class="notification-time">
                {{ formatDate(notification.created_at) }}
              </span>
              <span v-if="!notification.read_at" class="unread-badge">
                Non lue
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useNotificationsStore } from '@/stores/notifications'

const notificationsStore = useNotificationsStore()

// Méthodes
const markAsRead = async (notificationId) => {
  await notificationsStore.markAsRead(notificationId)
}

const markAllAsRead = async () => {
  await notificationsStore.markAllAsRead()
}

const deleteNotification = async (notificationId) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) {
    await notificationsStore.deleteNotification(notificationId)
  }
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'status-pending',
    in_progress: 'status-in-progress',
    done: 'status-done'
  }
  return classes[status] || 'status-pending'
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'En attente',
    in_progress: 'En cours',
    done: 'Terminé'
  }
  return labels[status] || 'En attente'
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = (now - date) / (1000 * 60 * 60)

  if (diffInHours < 1) {
    const diffInMinutes = Math.floor((now - date) / (1000 * 60))
    return `Il y a ${diffInMinutes} minute${diffInMinutes > 1 ? 's' : ''}`
  } else if (diffInHours < 24) {
    return `Il y a ${Math.floor(diffInHours)} heure${Math.floor(diffInHours) > 1 ? 's' : ''}`
  } else {
    return date.toLocaleDateString('fr-FR', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  }
}

// Lifecycle
onMounted(async () => {
  await notificationsStore.fetchNotifications()
  await notificationsStore.fetchUnreadCount()
})
</script>

<style scoped>
.notifications-container {
  max-width: 800px;
  margin: 0 auto;
}

.notifications-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e1e8ed;
}

.notifications-header h1 {
  margin: 0;
  color: #2c3e50;
  font-size: 2rem;
}

.header-actions {
  display: flex;
  gap: 1rem;
}

.notifications-stats {
  display: flex;
  gap: 2rem;
  margin-bottom: 2rem;
  padding: 1rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.stat-number {
  font-size: 2rem;
  font-weight: bold;
  color: #2c3e50;
}

.stat-number.unread {
  color: #e74c3c;
}

.stat-label {
  font-size: 0.9rem;
  color: #7f8c8d;
  text-transform: uppercase;
  font-weight: 600;
}

.notifications-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  overflow: hidden;
}

.loading {
  text-align: center;
  padding: 3rem;
  color: #7f8c8d;
}

.empty-state {
  text-align: center;
  padding: 3rem;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  margin: 0 0 0.5rem 0;
  color: #2c3e50;
}

.empty-state p {
  margin: 0;
  color: #7f8c8d;
}

.notifications-list {
  max-height: 600px;
  overflow-y: auto;
}

.notification-item {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  border-bottom: 1px solid #e1e8ed;
  transition: background-color 0.2s;
}

.notification-item:hover {
  background-color: #f8f9fa;
}

.notification-item.unread {
  background-color: #fff3cd;
  border-left: 4px solid #ffc107;
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-icon {
  font-size: 1.5rem;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ecf0f1;
  border-radius: 50%;
  flex-shrink: 0;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.5rem;
}

.notification-header h4 {
  margin: 0;
  color: #2c3e50;
  font-size: 1rem;
  line-height: 1.4;
}

.notification-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 4px;
  transition: background-color 0.2s;
  color: #7f8c8d;
}

.action-btn:hover {
  background-color: #e1e8ed;
}

.action-btn.delete:hover {
  background-color: #f8d7da;
  color: #e74c3c;
}

.notification-details {
  margin: 1rem 0;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  border-left: 3px solid #3498db;
}

.task-info {
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
  line-height: 1.4;
}

.task-info:last-child {
  margin-bottom: 0;
}

.task-info strong {
  color: #2c3e50;
}

.task-status {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 600;
}

.status-pending {
  background: #fff3cd;
  color: #856404;
}

.status-in-progress {
  background: #d1ecf1;
  color: #0c5460;
}

.status-done {
  background: #d4edda;
  color: #155724;
}

.notification-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1rem;
}

.notification-time {
  font-size: 0.8rem;
  color: #95a5a6;
}

.unread-badge {
  background: #e74c3c;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
}

.btn-secondary {
  background: #ecf0f1;
  color: #2c3e50;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-secondary:hover:not(:disabled) {
  background: #d5dbdb;
}

.btn-secondary:disabled {
  background: #bdc3c7;
  color: #95a5a6;
  cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
  .notifications-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .notifications-stats {
    flex-direction: column;
    gap: 1rem;
  }
  
  .notification-item {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .notification-header {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .notification-actions {
    align-self: flex-end;
  }
  
  .notification-footer {
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-start;
  }
}

/* Scrollbar personnalisée */
.notifications-list::-webkit-scrollbar {
  width: 6px;
}

.notifications-list::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.notifications-list::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.notifications-list::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>
