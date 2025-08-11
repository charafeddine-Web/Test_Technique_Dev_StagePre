<template>
  <div class="profile-container">
    <div class="profile-header">
      <h1>Mon Profil</h1>
    </div>

    <div class="profile-content">
      <div class="profile-card">
        <div class="profile-avatar">
          <img 
            v-if="authStore.user?.image" 
            :src="authStore.user.image" 
            :alt="authStore.user?.full_name"
            class="avatar-image"
          />
          <div v-else class="avatar-placeholder">
            {{ getInitials(authStore.user?.full_name) }}
          </div>
        </div>

        <div class="profile-info">
          <h2>{{ authStore.user?.full_name }}</h2>
          <p class="user-email">{{ authStore.user?.email }}</p>
          
          <div class="profile-details">
            <div class="detail-item">
              <span class="detail-label">📞 Téléphone:</span>
              <span class="detail-value">{{ authStore.user?.phone_number || 'Non renseigné' }}</span>
            </div>
            
            <div class="detail-item">
              <span class="detail-label">📍 Adresse:</span>
              <span class="detail-value">{{ authStore.user?.address || 'Non renseignée' }}</span>
            </div>
            
            <div class="detail-item">
              <span class="detail-label">📅 Membre depuis:</span>
              <span class="detail-value">{{ formatDate(authStore.user?.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="profile-actions">
        <button @click="showEditModal = true" class="btn-primary">
          ✏️ Modifier le profil
        </button>
        <button @click="showPasswordModal = true" class="btn-secondary">
          🔒 Changer le mot de passe
        </button>
      </div>
    </div>

    <!-- Modal de modification du profil -->
    <div v-if="showEditModal" class="modal-overlay" @click="showEditModal = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Modifier le profil</h3>
          <button @click="showEditModal = false" class="modal-close">&times;</button>
        </div>

        <form @submit.prevent="updateProfile" class="modal-form">
          <div class="form-group">
            <label for="edit-full-name">Nom complet *</label>
            <input
              id="edit-full-name"
              v-model="editForm.full_name"
              type="text"
              required
              placeholder="Votre nom complet"
            />
          </div>

          <div class="form-group">
            <label for="edit-email">Email *</label>
            <input
              id="edit-email"
              v-model="editForm.email"
              type="email"
              required
              placeholder="votre@email.com"
            />
          </div>

          <div class="form-group">
            <label for="edit-phone">Numéro de téléphone</label>
            <input
              id="edit-phone"
              v-model="editForm.phone_number"
              type="tel"
              placeholder="+1234567890"
            />
          </div>

          <div class="form-group">
            <label for="edit-address">Adresse</label>
            <textarea
              id="edit-address"
              v-model="editForm.address"
              rows="3"
              placeholder="Votre adresse complète"
            ></textarea>
          </div>

          <div class="form-group">
            <label for="edit-image">Image de profil (URL)</label>
            <input
              id="edit-image"
              v-model="editForm.image"
              type="url"
              placeholder="https://example.com/image.jpg"
            />
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

    <!-- Modal de changement de mot de passe -->
    <div v-if="showPasswordModal" class="modal-overlay" @click="showPasswordModal = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Changer le mot de passe</h3>
          <button @click="showPasswordModal = false" class="modal-close">&times;</button>
        </div>

        <form @submit.prevent="changePassword" class="modal-form">
          <div class="form-group">
            <label for="current-password">Mot de passe actuel *</label>
            <input
              id="current-password"
              v-model="passwordForm.current_password"
              type="password"
              required
              placeholder="Votre mot de passe actuel"
            />
          </div>

          <div class="form-group">
            <label for="new-password">Nouveau mot de passe *</label>
            <input
              id="new-password"
              v-model="passwordForm.new_password"
              type="password"
              required
              placeholder="Votre nouveau mot de passe"
            />
          </div>

          <div class="form-group">
            <label for="confirm-password">Confirmer le nouveau mot de passe *</label>
            <input
              id="confirm-password"
              v-model="passwordForm.new_password_confirmation"
              type="password"
              required
              placeholder="Confirmez votre nouveau mot de passe"
            />
          </div>

          <div class="modal-actions">
            <button type="button" @click="showPasswordModal = false" class="btn-secondary">
              Annuler
            </button>
            <button type="submit" :disabled="submittingPassword" class="btn-primary">
              {{ submittingPassword ? 'Modification...' : 'Changer le mot de passe' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'

const authStore = useAuthStore()

// State
const showEditModal = ref(false)
const showPasswordModal = ref(false)
const submitting = ref(false)
const submittingPassword = ref(false)

const editForm = reactive({
  full_name: authStore.user?.full_name || '',
  email: authStore.user?.email || '',
  phone_number: authStore.user?.phone_number || '',
  address: authStore.user?.address || '',
  image: authStore.user?.image || ''
})

const passwordForm = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

// Méthodes
const getInitials = (fullName) => {
  if (!fullName) return '?'
  return fullName
    .split(' ')
    .map(name => name.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const formatDate = (dateString) => {
  if (!dateString) return 'Date inconnue'
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const updateProfile = async () => {
  submitting.value = true

  try {
    const response = await axios.put('/auth/profile', editForm)
    
    if (response.data.success) {
      // Mettre à jour les données utilisateur dans le store
      authStore.user = response.data.data.user
      
      showEditModal.value = false
      if (window.showToast) {
        window.showToast('Profil modifié avec succès !', 'success')
      }
    }
  } catch (error) {
    console.error('Erreur updateProfile:', error)
    if (window.showToast) {
      window.showToast(
        error.response?.data?.message || 'Erreur lors de la modification du profil',
        'error'
      )
    }
  } finally {
    submitting.value = false
  }
}

const changePassword = async () => {
  if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
    if (window.showToast) {
      window.showToast('Les mots de passe ne correspondent pas', 'error')
    }
    return
  }

  submittingPassword.value = true

  try {
    const response = await axios.put('/auth/password', {
      current_password: passwordForm.current_password,
      new_password: passwordForm.new_password,
      new_password_confirmation: passwordForm.new_password_confirmation
    })
    
    if (response.data.success) {
      showPasswordModal.value = false
      
      // Réinitialiser le formulaire
      passwordForm.current_password = ''
      passwordForm.new_password = ''
      passwordForm.new_password_confirmation = ''
      
      if (window.showToast) {
        window.showToast('Mot de passe modifié avec succès !', 'success')
      }
    }
  } catch (error) {
    console.error('Erreur changePassword:', error)
    if (window.showToast) {
      window.showToast(
        error.response?.data?.message || 'Erreur lors du changement de mot de passe',
        'error'
      )
    }
  } finally {
    submittingPassword.value = false
  }
}
</script>

<style scoped>
.profile-container {
  max-width: 800px;
  margin: 0 auto;
}

.profile-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e1e8ed;
}

.profile-header h1 {
  margin: 0;
  color: #2c3e50;
  font-size: 2rem;
}

.profile-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  overflow: hidden;
}

.profile-card {
  padding: 2rem;
  display: flex;
  gap: 2rem;
  align-items: flex-start;
}

.profile-avatar {
  flex-shrink: 0;
}

.avatar-image {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #ecf0f1;
}

.avatar-placeholder {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 2.5rem;
  font-weight: bold;
  border: 4px solid #ecf0f1;
}

.profile-info {
  flex: 1;
}

.profile-info h2 {
  margin: 0 0 0.5rem 0;
  color: #2c3e50;
  font-size: 1.8rem;
}

.user-email {
  margin: 0 0 1.5rem 0;
  color: #7f8c8d;
  font-size: 1.1rem;
}

.profile-details {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.detail-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.detail-label {
  font-weight: 600;
  color: #2c3e50;
  min-width: 120px;
}

.detail-value {
  color: #7f8c8d;
  flex: 1;
}

.profile-actions {
  padding: 1.5rem 2rem;
  border-top: 1px solid #e1e8ed;
  display: flex;
  gap: 1rem;
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
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e1e8ed;
  border-radius: 8px;
  font-size: 1rem;
  font-family: inherit;
}

.form-group input:focus,
.form-group textarea:focus {
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
  margin-top: 2rem;
}

/* Responsive */
@media (max-width: 768px) {
  .profile-card {
    flex-direction: column;
    text-align: center;
    gap: 1.5rem;
  }
  
  .profile-avatar {
    align-self: center;
  }
  
  .detail-item {
    flex-direction: column;
    gap: 0.5rem;
    text-align: left;
  }
  
  .detail-label {
    min-width: auto;
  }
  
  .profile-actions {
    flex-direction: column;
  }
  
  .modal-actions {
    flex-direction: column;
  }
  
  .modal-actions button {
    width: 100%;
  }
}
</style>
