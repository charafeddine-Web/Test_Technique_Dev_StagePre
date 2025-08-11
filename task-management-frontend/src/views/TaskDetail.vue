<template>
  <div class="task-detail-container">
    <div class="task-detail-header">
      <div class="header-left">
        <router-link to="/tasks" class="back-link">
          ← Retour aux tâches
        </router-link>
        <h1>Détail de la tâche</h1>
      </div>
      <div class="header-actions">
        <button @click="showEditModal = true" class="btn-secondary">
          ✏️ Modifier
        </button>
        <button @click="deleteTask" class="btn-danger">
          🗑️ Supprimer
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading">
      Chargement de la tâche...
    </div>

    <div v-else-if="error" class="error-state">
      <div class="error-icon">❌</div>
      <h3>Erreur</h3>
      <p>{{ error }}</p>
      <router-link to="/tasks" class="btn-primary">
        Retour aux tâches
      </router-link>
    </div>

    <div v-else-if="task" class="task-detail-content">
      <div class="task-main">
        <div class="task-header">
          <h2>{{ task.title }}</h2>
          <div class="task-status" :class="getStatusClass(task.status)">
            {{ getStatusLabel(task.status) }}
          </div>
        </div>

        <div class="task-description">
          <h3>Description</h3>
          <p>{{ task.description || 'Aucune description' }}</p>
        </div>

        <div class="task-meta">
          <div class="meta-item">
            <span class="meta-label">Créée le:</span>
            <span class="meta-value">{{ formatDate(task.created_at) }}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Modifiée le:</span>
            <span class="meta-value">{{ formatDate(task.updated_at) }}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">ID de la tâche:</span>
            <span class="meta-value">{{ task.id }}</span>
          </div>
        </div>
      </div>

      <div class="task-actions-panel">
        <h3>Actions</h3>
        <div class="actions-list">
          <button 
            @click="changeStatus('pending')"
            :class="['status-btn', { active: task.status === 'pending' }]"
          >
            📋 En attente
          </button>
          <button 
            @click="changeStatus('in_progress')"
            :class="['status-btn', { active: task.status === 'in_progress' }]"
          >
            🔄 En cours
          </button>
          <button 
            @click="changeStatus('done')"
            :class="['status-btn', { active: task.status === 'done' }]"
          >
            ✅ Terminé
          </button>
        </div>
      </div>
    </div>

    <!-- Modal d'édition -->
    <div v-if="showEditModal" class="modal-overlay" @click="showEditModal = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Modifier la tâche</h3>
          <button @click="showEditModal = false" class="modal-close">&times;</button>
        </div>

        <form @submit.prevent="updateTask" class="modal-form">
          <div class="form-group">
            <label for="edit-title">Titre *</label>
            <input
              id="edit-title"
              v-model="editForm.title"
              type="text"
              required
              placeholder="Titre de la tâche"
            />
          </div>

          <div class="form-group">
            <label for="edit-description">Description</label>
            <textarea
              id="edit-description"
              v-model="editForm.description"
              rows="4"
              placeholder="Description de la tâche"
            ></textarea>
          </div>

          <div class="form-group">
            <label for="edit-status">Statut</label>
            <select id="edit-status" v-model="editForm.status">
              <option value="pending">En attente</option>
              <option value="in_progress">En cours</option>
              <option value="done">Terminé</option>
            </select>
          </div>

          <div class="modal-actions">
            <button type="button" @click="showEditModal = false" class="btn-secondary">
              Annuler
            </button>
            <button type="submit" :disabled="submitting" class="btn-primary">
              {{ submitting ? 'Modification...' : 'Modifier' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useTasksStore } from '@/stores/tasks'

const route = useRoute()
const router = useRouter()
const tasksStore = useTasksStore()

// State
const task = ref(null)
const loading = ref(true)
const error = ref(null)
const showEditModal = ref(false)
const submitting = ref(false)

const editForm = ref({
  title: '',
  description: '',
  status: 'pending'
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
  return new Date(dateString).toLocaleString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const loadTask = async () => {
  loading.value = true
  error.value = null

  try {
    const result = await tasksStore.getTask(route.params.id)
    
    if (result.success) {
      task.value = result.data
      editForm.value = {
        title: result.data.title,
        description: result.data.description,
        status: result.data.status
      }
    } else {
      error.value = result.message
    }
  } catch (err) {
    error.value = 'Erreur lors du chargement de la tâche'
    console.error('Erreur loadTask:', err)
  } finally {
    loading.value = false
  }
}

const changeStatus = async (newStatus) => {
  if (!task.value || task.value.status === newStatus) return

  try {
    const result = await tasksStore.updateTask(task.value.id, {
      ...task.value,
      status: newStatus
    })

    if (result.success) {
      task.value = result.data
      if (window.showToast) {
        window.showToast(`Statut changé vers "${getStatusLabel(newStatus)}"`, 'success')
      }
    }
  } catch (err) {
    console.error('Erreur changeStatus:', err)
    if (window.showToast) {
      window.showToast('Erreur lors du changement de statut', 'error')
    }
  }
}

const updateTask = async () => {
  if (!task.value || !editForm.value.title.trim()) return

  submitting.value = true

  try {
    const result = await tasksStore.updateTask(task.value.id, {
      title: editForm.value.title,
      description: editForm.value.description,
      status: editForm.value.status
    })

    if (result.success) {
      task.value = result.data
      showEditModal.value = false
      if (window.showToast) {
        window.showToast('Tâche modifiée avec succès !', 'success')
      }
    }
  } catch (err) {
    console.error('Erreur updateTask:', err)
    if (window.showToast) {
      window.showToast('Erreur lors de la modification', 'error')
    }
  } finally {
    submitting.value = false
  }
}

const deleteTask = async () => {
  if (!task.value) return

  if (confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?')) {
    try {
      const result = await tasksStore.deleteTask(task.value.id)
      
      if (result.success) {
        if (window.showToast) {
          window.showToast('Tâche supprimée avec succès !', 'success')
        }
        router.push('/tasks')
      }
    } catch (err) {
      console.error('Erreur deleteTask:', err)
      if (window.showToast) {
        window.showToast('Erreur lors de la suppression', 'error')
      }
    }
  }
}

// Lifecycle
onMounted(() => {
  loadTask()
})
</script>

<style scoped>
.task-detail-container {
  max-width: 1000px;
  margin: 0 auto;
}

.task-detail-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e1e8ed;
}

.header-left {
  flex: 1;
}

.back-link {
  display: inline-block;
  color: #3498db;
  text-decoration: none;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.back-link:hover {
  text-decoration: underline;
}

.task-detail-header h1 {
  margin: 0;
  color: #2c3e50;
  font-size: 2rem;
}

.header-actions {
  display: flex;
  gap: 1rem;
}

.loading {
  text-align: center;
  padding: 3rem;
  color: #7f8c8d;
}

.error-state {
  text-align: center;
  padding: 3rem;
}

.error-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.error-state h3 {
  margin: 0 0 0.5rem 0;
  color: #2c3e50;
}

.error-state p {
  margin: 0 0 1.5rem 0;
  color: #7f8c8d;
}

.task-detail-content {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
}

.task-main {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.task-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
}

.task-header h2 {
  margin: 0;
  color: #2c3e50;
  font-size: 1.8rem;
  flex: 1;
  margin-right: 1rem;
}

.task-status {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 600;
  white-space: nowrap;
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
  margin-bottom: 2rem;
}

.task-description h3 {
  margin: 0 0 1rem 0;
  color: #2c3e50;
  font-size: 1.2rem;
}

.task-description p {
  margin: 0;
  color: #7f8c8d;
  line-height: 1.6;
  font-size: 1rem;
}

.task-meta {
  border-top: 1px solid #e1e8ed;
  padding-top: 1.5rem;
}

.meta-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.75rem;
}

.meta-item:last-child {
  margin-bottom: 0;
}

.meta-label {
  font-weight: 600;
  color: #2c3e50;
}

.meta-value {
  color: #7f8c8d;
}

.task-actions-panel {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  height: fit-content;
}

.task-actions-panel h3 {
  margin: 0 0 1rem 0;
  color: #2c3e50;
  font-size: 1.2rem;
}

.actions-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.status-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border: 2px solid #e1e8ed;
  border-radius: 8px;
  background: white;
  color: #2c3e50;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  text-align: left;
}

.status-btn:hover {
  border-color: #3498db;
  background: #f8f9fa;
}

.status-btn.active {
  border-color: #3498db;
  background: #3498db;
  color: white;
}

.btn-primary,
.btn-secondary,
.btn-danger {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.3s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
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

.btn-danger {
  background: #e74c3c;
  color: white;
}

.btn-danger:hover {
  background: #c0392b;
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

/* Responsive */
@media (max-width: 768px) {
  .task-detail-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .header-actions {
    width: 100%;
    justify-content: flex-start;
  }
  
  .task-detail-content {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .task-header {
    flex-direction: column;
    gap: 1rem;
  }
  
  .task-header h2 {
    margin-right: 0;
  }
  
  .modal-actions {
    flex-direction: column;
  }
  
  .modal-actions button {
    width: 100%;
  }
}
</style>
