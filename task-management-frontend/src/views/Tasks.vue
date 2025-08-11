<template>
  <div class="tasks-container">
    <div class="tasks-header">
      <h1>Mes Tâches</h1>
      <button @click="showCreateModal = true" class="btn-primary">
        <span>➕</span>
        Nouvelle tâche
      </button>
    </div>

    <!-- Filtres -->
    <div class="filters">
      <div class="filter-group">
        <label>Statut:</label>
        <select v-model="statusFilter" class="filter-select">
          <option value="">Tous</option>
          <option value="pending">En attente</option>
          <option value="in_progress">En cours</option>
          <option value="done">Terminé</option>
        </select>
      </div>
      
      <div class="filter-group">
        <label>Recherche:</label>
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Rechercher dans les tâches..."
          class="filter-input"
        />
      </div>
    </div>

    <!-- Statistiques -->
    <div class="tasks-stats">
      <div class="stat-item">
        <span class="stat-number">{{ filteredTasks.length }}</span>
        <span class="stat-label">Tâches affichées</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">{{ pendingTasks.length }}</span>
        <span class="stat-label">En attente</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">{{ inProgressTasks.length }}</span>
        <span class="stat-label">En cours</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">{{ doneTasks.length }}</span>
        <span class="stat-label">Terminées</span>
      </div>
    </div>

    <!-- Liste des tâches -->
    <div class="tasks-content">
      <div v-if="tasksStore.loading" class="loading">
        Chargement des tâches...
      </div>

      <div v-else-if="filteredTasks.length === 0" class="empty-state">
        <div class="empty-icon">📝</div>
        <h3>Aucune tâche trouvée</h3>
        <p v-if="searchQuery || statusFilter">
          Aucune tâche ne correspond à vos critères de recherche
        </p>
        <p v-else>
          Commencez par créer votre première tâche
        </p>
        <button @click="showCreateModal = true" class="btn-primary">
          Créer une tâche
        </button>
      </div>

      <div v-else class="tasks-grid">
        <div
          v-for="task in filteredTasks"
          :key="task.id"
          class="task-card"
          :class="getStatusClass(task.status)"
        >
          <div class="task-header">
            <h3>{{ task.title }}</h3>
            <div class="task-actions">
              <button 
                @click="editTask(task)"
                class="action-btn edit"
                title="Modifier"
              >
                ✏️
              </button>
              <button 
                @click="deleteTask(task.id)"
                class="action-btn delete"
                title="Supprimer"
              >
                🗑️
              </button>
            </div>
          </div>
          
          <p class="task-description">{{ task.description }}</p>
          
          <div class="task-status-badge" :class="getStatusClass(task.status)">
            {{ getStatusLabel(task.status) }}
          </div>
          
          <div class="task-footer">
            <span class="task-date">
              Créée le {{ formatDate(task.created_at) }}
            </span>
            <router-link :to="`/tasks/${task.id}`" class="task-link">
              Voir détails
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de création/édition -->
    <div v-if="showCreateModal || showEditModal" class="modal-overlay" @click="closeModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>{{ showEditModal ? 'Modifier la tâche' : 'Nouvelle tâche' }}</h3>
          <button @click="closeModal" class="modal-close">&times;</button>
        </div>

        <form @submit.prevent="showEditModal ? updateTask() : createTask()" class="modal-form">
          <div class="form-group">
            <label for="task-title">Titre *</label>
            <input
              id="task-title"
              v-model="taskForm.title"
              type="text"
              required
              placeholder="Titre de la tâche"
            />
          </div>

          <div class="form-group">
            <label for="task-description">Description</label>
            <textarea
              id="task-description"
              v-model="taskForm.description"
              rows="4"
              placeholder="Description de la tâche"
            ></textarea>
          </div>

          <div class="form-group">
            <label for="task-status">Statut</label>
            <select id="task-status" v-model="taskForm.status">
              <option value="pending">En attente</option>
              <option value="in_progress">En cours</option>
              <option value="done">Terminé</option>
            </select>
          </div>

          <div class="modal-actions">
            <button type="button" @click="closeModal" class="btn-secondary">
              Annuler
            </button>
            <button type="submit" :disabled="submitting" class="btn-primary">
              {{ submitting ? 'Enregistrement...' : (showEditModal ? 'Modifier' : 'Créer') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useTasksStore } from '@/stores/tasks'

const tasksStore = useTasksStore()

// State
const showCreateModal = ref(false)
const showEditModal = ref(false)
const submitting = ref(false)
const statusFilter = ref('')
const searchQuery = ref('')
const editingTask = ref(null)

const taskForm = ref({
  title: '',
  description: '',
  status: 'pending'
})

// Computed properties
const filteredTasks = computed(() => {
  let tasks = tasksStore.tasks

  // Filtre par statut
  if (statusFilter.value) {
    tasks = tasks.filter(task => task.status === statusFilter.value)
  }

  // Filtre par recherche
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    tasks = tasks.filter(task => 
      task.title.toLowerCase().includes(query) ||
      task.description.toLowerCase().includes(query)
    )
  }

  return tasks
})

const pendingTasks = computed(() => tasksStore.getTasksByStatus('pending'))
const inProgressTasks = computed(() => tasksStore.getTasksByStatus('in_progress'))
const doneTasks = computed(() => tasksStore.getTasksByStatus('done'))

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

const resetForm = () => {
  taskForm.value = {
    title: '',
    description: '',
    status: 'pending'
  }
  editingTask.value = null
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  resetForm()
}

const editTask = (task) => {
  editingTask.value = task
  taskForm.value = {
    title: task.title,
    description: task.description,
    status: task.status
  }
  showEditModal.value = true
}

const createTask = async () => {
  if (!taskForm.value.title.trim()) return

  submitting.value = true

  try {
    const result = await tasksStore.createTask({
      title: taskForm.value.title,
      description: taskForm.value.description,
      status: taskForm.value.status
    })

    if (result.success) {
      closeModal()
      if (window.showToast) {
        window.showToast('Tâche créée avec succès !', 'success')
      }
    }
  } catch (error) {
    console.error('Erreur lors de la création de la tâche:', error)
    if (window.showToast) {
      window.showToast('Erreur lors de la création de la tâche', 'error')
    }
  } finally {
    submitting.value = false
  }
}

const updateTask = async () => {
  if (!taskForm.value.title.trim() || !editingTask.value) return

  submitting.value = true

  try {
    const result = await tasksStore.updateTask(editingTask.value.id, {
      title: taskForm.value.title,
      description: taskForm.value.description,
      status: taskForm.value.status
    })

    if (result.success) {
      closeModal()
      if (window.showToast) {
        window.showToast('Tâche modifiée avec succès !', 'success')
      }
    }
  } catch (error) {
    console.error('Erreur lors de la modification de la tâche:', error)
    if (window.showToast) {
      window.showToast('Erreur lors de la modification de la tâche', 'error')
    }
  } finally {
    submitting.value = false
  }
}

const deleteTask = async (taskId) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?')) {
    try {
      const result = await tasksStore.deleteTask(taskId)
      if (result.success) {
        if (window.showToast) {
          window.showToast('Tâche supprimée avec succès !', 'success')
        }
      }
    } catch (error) {
      console.error('Erreur lors de la suppression de la tâche:', error)
      if (window.showToast) {
        window.showToast('Erreur lors de la suppression de la tâche', 'error')
      }
    }
  }
}

// Lifecycle
onMounted(async () => {
  await tasksStore.fetchTasks()
})
</script>

<style scoped>
.tasks-container {
  max-width: 1200px;
  margin: 0 auto;
}

.tasks-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e1e8ed;
}

.tasks-header h1 {
  margin: 0;
  color: #2c3e50;
  font-size: 2rem;
}

.filters {
  display: flex;
  gap: 2rem;
  margin-bottom: 2rem;
  padding: 1rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-group label {
  font-weight: 600;
  color: #2c3e50;
  font-size: 0.9rem;
}

.filter-select,
.filter-input {
  padding: 0.5rem;
  border: 2px solid #e1e8ed;
  border-radius: 8px;
  font-size: 1rem;
  min-width: 200px;
}

.filter-select:focus,
.filter-input:focus {
  outline: none;
  border-color: #3498db;
}

.tasks-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-item {
  background: white;
  padding: 1rem;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  text-align: center;
}

.stat-number {
  display: block;
  font-size: 2rem;
  font-weight: bold;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 0.9rem;
  color: #7f8c8d;
  text-transform: uppercase;
  font-weight: 600;
}

.tasks-content {
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
  margin: 0 0 1.5rem 0;
  color: #7f8c8d;
}

.tasks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
  padding: 1.5rem;
}

.task-card {
  border: 1px solid #e1e8ed;
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.3s;
  position: relative;
}

.task-card:hover {
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  transform: translateY(-2px);
}

.task-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.task-header h3 {
  margin: 0;
  font-size: 1.2rem;
  color: #2c3e50;
  flex: 1;
  margin-right: 1rem;
}

.task-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 6px;
  transition: background-color 0.2s;
}

.action-btn:hover {
  background-color: #f8f9fa;
}

.action-btn.delete:hover {
  background-color: #f8d7da;
}

.task-description {
  margin: 0 0 1rem 0;
  color: #7f8c8d;
  font-size: 0.9rem;
  line-height: 1.5;
}

.task-status-badge {
  display: inline-block;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  margin-bottom: 1rem;
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

.task-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e1e8ed;
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
  margin-bottom: 1.5rem;
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
  min-height: 100px;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
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
  display: flex;
  align-items: center;
  gap: 0.5rem;
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
  .tasks-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .filters {
    flex-direction: column;
    gap: 1rem;
  }
  
  .filter-select,
  .filter-input {
    min-width: auto;
  }
  
  .tasks-grid {
    grid-template-columns: 1fr;
    padding: 1rem;
  }
  
  .modal-actions {
    flex-direction: column;
  }
  
  .modal-actions button {
    width: 100%;
  }
}
</style>
