<template>
  <div class="home-container">
    <div class="welcome-section">
      <h1>Bienvenue, {{ authStore.user?.full_name }} !</h1>
      <p>Gérez vos tâches et restez organisé</p>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon pending">📋</div>
        <div class="stat-content">
          <h3>{{ pendingTasks.length }}</h3>
          <p>Tâches en attente</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon in-progress">🔄</div>
        <div class="stat-content">
          <h3>{{ inProgressTasks.length }}</h3>
          <p>Tâches en cours</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon done">✅</div>
        <div class="stat-content">
          <h3>{{ doneTasks.length }}</h3>
          <p>Tâches terminées</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon notifications">🔔</div>
        <div class="stat-content">
          <h3>{{ notificationsStore.unreadCount }}</h3>
          <p>Notifications non lues</p>
        </div>
      </div>
    </div>

    <!-- Actions rapides -->
    <div class="quick-actions">
      <h2>Actions rapides</h2>
      <div class="actions-grid">
        <button @click="showCreateTaskModal = true" class="action-btn primary">
          <span class="action-icon">➕</span>
          <span>Nouvelle tâche</span>
        </button>
        
        <router-link to="/tasks" class="action-btn secondary">
          <span class="action-icon">📝</span>
          <span>Voir toutes les tâches</span>
        </router-link>
        
        <router-link to="/notifications" class="action-btn secondary">
          <span class="action-icon">🔔</span>
          <span>Notifications</span>
        </router-link>
        
        <router-link to="/profile" class="action-btn secondary">
          <span class="action-icon">👤</span>
          <span>Profil</span>
        </router-link>
      </div>
    </div>

    <!-- Tâches récentes -->
    <div class="recent-tasks">
      <div class="section-header">
        <h2>Tâches récentes</h2>
        <router-link to="/tasks" class="view-all-link">Voir tout</router-link>
      </div>

      <div v-if="tasksStore.loading" class="loading">
        Chargement des tâches...
      </div>

      <div v-else-if="tasksStore.tasks.length === 0" class="empty-state">
        <div class="empty-icon">📝</div>
        <h3>Aucune tâche</h3>
        <p>Commencez par créer votre première tâche</p>
        <button @click="showCreateTaskModal = true" class="btn-primary">
          Créer une tâche
        </button>
      </div>

      <div v-else class="tasks-grid">
        <div
          v-for="task in recentTasks"
          :key="task.id"
          class="task-card"
          :class="getStatusClass(task.status)"
        >
          <div class="task-header">
            <h3>{{ task.title }}</h3>
            <span class="task-status" :class="getStatusClass(task.status)">
              {{ getStatusLabel(task.status) }}
            </span>
          </div>
          
          <p class="task-description">{{ task.description }}</p>
          
          <div class="task-footer">
            <span class="task-date">
              {{ formatDate(task.created_at) }}
            </span>
            <router-link :to="`/tasks/${task.id}`" class="task-link">
              Voir détails
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de création de tâche -->
    <div v-if="showCreateTaskModal" class="modal-overlay" @click="showCreateTaskModal = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Nouvelle tâche</h3>
          <button @click="showCreateTaskModal = false" class="modal-close">&times;</button>
        </div>

        <form @submit.prevent="createTask" class="modal-form">
          <div class="form-group">
            <label for="task-title">Titre</label>
            <input
              id="task-title"
              v-model="newTask.title"
              type="text"
              required
              placeholder="Titre de la tâche"
            />
          </div>

          <div class="form-group">
            <label for="task-description">Description</label>
            <textarea
              id="task-description"
              v-model="newTask.description"
              rows="3"
              placeholder="Description de la tâche"
            ></textarea>
          </div>

          <div class="form-group">
            <label for="task-status">Statut</label>
            <select id="task-status" v-model="newTask.status">
              <option value="pending">En attente</option>
              <option value="in_progress">En cours</option>
              <option value="done">Terminé</option>
            </select>
          </div>

          <div class="modal-actions">
            <button type="button" @click="showCreateTaskModal = false" class="btn-secondary">
              Annuler
            </button>
            <button type="submit" :disabled="creatingTask" class="btn-primary">
              {{ creatingTask ? 'Création...' : 'Créer la tâche' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useTasksStore } from '@/stores/tasks'
import { useNotificationsStore } from '@/stores/notifications'

const authStore = useAuthStore()
const tasksStore = useTasksStore()
const notificationsStore = useNotificationsStore()

const showCreateTaskModal = ref(false)
const creatingTask = ref(false)

const newTask = ref({
  title: '',
  description: '',
  status: 'pending'
})

// Computed properties
const pendingTasks = computed(() => tasksStore.getTasksByStatus('pending'))
const inProgressTasks = computed(() => tasksStore.getTasksByStatus('in_progress'))
const doneTasks = computed(() => tasksStore.getTasksByStatus('done'))

const recentTasks = computed(() => {
  return tasksStore.tasks.slice(0, 6) // Afficher les 6 tâches les plus récentes
})

// Méthodes
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
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

const createTask = async () => {
  if (!newTask.value.title.trim()) return

  creatingTask.value = true

  try {
    const result = await tasksStore.createTask({
      title: newTask.value.title,
      description: newTask.value.description,
      status: newTask.value.status
    })

    if (result.success) {
      showCreateTaskModal.value = false
      newTask.value = { title: '', description: '', status: 'pending' }
      
      // Afficher une notification de succès
      if (window.showToast) {
        window.showToast('Tâche créée avec succès !', 'success')
      }
    }
  } catch (error) {
    console.error('Erreur lors de la création de la tâche:', error)
  } finally {
    creatingTask.value = false
  }
}

// Lifecycle
onMounted(async () => {
  await tasksStore.fetchTasks()
  await notificationsStore.fetchUnreadCount()
})
</script>

<style scoped>
.home-container {
  max-width: 1200px;
  margin: 0 auto;
}

.welcome-section {
  text-align: center;
  margin-bottom: 2rem;
  padding: 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 12px;
}

.welcome-section h1 {
  margin: 0 0 0.5rem 0;
  font-size: 2.5rem;
}

.welcome-section p {
  margin: 0;
  font-size: 1.1rem;
  opacity: 0.9;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  font-size: 2rem;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
}

.stat-icon.pending {
  background: #fff3cd;
  color: #856404;
}

.stat-icon.in-progress {
  background: #d1ecf1;
  color: #0c5460;
}

.stat-icon.done {
  background: #d4edda;
  color: #155724;
}

.stat-icon.notifications {
  background: #f8d7da;
  color: #721c24;
}

.stat-content h3 {
  margin: 0;
  font-size: 2rem;
  font-weight: bold;
  color: #2c3e50;
}

.stat-content p {
  margin: 0;
  color: #7f8c8d;
  font-size: 0.9rem;
}

.quick-actions {
  margin-bottom: 2rem;
}

.quick-actions h2 {
  margin-bottom: 1rem;
  color: #2c3e50;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1.5rem;
  border: none;
  border-radius: 12px;
  text-decoration: none;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.action-btn.primary {
  background: #3498db;
  color: white;
}

.action-btn.secondary {
  background: #ecf0f1;
  color: #2c3e50;
}

.action-icon {
  font-size: 2rem;
}

.recent-tasks {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-header h2 {
  margin: 0;
  color: #2c3e50;
}

.view-all-link {
  color: #3498db;
  text-decoration: none;
  font-weight: 600;
}

.view-all-link:hover {
  text-decoration: underline;
}

.loading {
  text-align: center;
  padding: 2rem;
  color: #7f8c8d;
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
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
  margin: 0 0 1.5rem 0;
  color: #7f8c8d;
}

.tasks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.task-card {
  border: 1px solid #e1e8ed;
  border-radius: 8px;
  padding: 1rem;
  transition: box-shadow 0.2s;
}

.task-card:hover {
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.task-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.5rem;
}

.task-header h3 {
  margin: 0;
  font-size: 1.1rem;
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

.task-description {
  margin: 0 0 1rem 0;
  color: #7f8c8d;
  font-size: 0.9rem;
  line-height: 1.4;
}

.task-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.task-date {
  font-size: 0.8rem;
  color: #95a5a6;
}

.task-link {
  color: #3498db;
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 600;
}

.task-link:hover {
  text-decoration: underline;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e1e8ed;
}

.modal-header h3 {
  margin: 0;
  color: #2c3e50;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #7f8c8d;
}

.modal-form {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #2c3e50;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e1e8ed;
  border-radius: 8px;
  font-size: 1rem;
  font-family: inherit;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #3498db;
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
}

.btn-primary,
.btn-secondary {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-primary {
  background: #3498db;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #2980b9;
}

.btn-primary:disabled {
  background: #bdc3c7;
  cursor: not-allowed;
}

.btn-secondary {
  background: #ecf0f1;
  color: #2c3e50;
}

.btn-secondary:hover {
  background: #d5dbdb;
}

/* Responsive */
@media (max-width: 768px) {
  .welcome-section h1 {
    font-size: 2rem;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .actions-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .tasks-grid {
    grid-template-columns: 1fr;
  }
  
  .modal-actions {
    flex-direction: column;
  }
  
  .modal-actions button {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .actions-grid {
    grid-template-columns: 1fr;
  }
}
</style>
