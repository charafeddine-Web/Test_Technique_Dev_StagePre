<template>
  <div class="register-container">
    <div class="register-card">
      <div class="register-header">
        <h1>Inscription</h1>
        <p>Créez votre compte</p>
      </div>

      <form @submit.prevent="handleRegister" class="register-form">
        <div class="form-group">
          <label for="full_name">Nom complet</label>
          <input
            id="full_name"
            v-model="form.full_name"
            type="text"
            required
            placeholder="Votre nom complet"
            :class="{ 'error': errors.full_name }"
          />
          <span v-if="errors.full_name" class="error-message">{{ errors.full_name }}</span>
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            placeholder="votre@email.com"
            :class="{ 'error': errors.email }"
          />
          <span v-if="errors.email" class="error-message">{{ errors.email }}</span>
        </div>

        <div class="form-group">
          <label for="phone_number">Numéro de téléphone</label>
          <input
            id="phone_number"
            v-model="form.phone_number"
            type="tel"
            required
            placeholder="+1234567890"
            :class="{ 'error': errors.phone_number }"
          />
          <span v-if="errors.phone_number" class="error-message">{{ errors.phone_number }}</span>
        </div>

        <div class="form-group">
          <label for="address">Adresse</label>
          <textarea
            id="address"
            v-model="form.address"
            required
            placeholder="Votre adresse complète"
            rows="3"
            :class="{ 'error': errors.address }"
          ></textarea>
          <span v-if="errors.address" class="error-message">{{ errors.address }}</span>
        </div>

        <div class="form-group">
          <label for="image">Image de profil (URL)</label>
          <input
            id="image"
            v-model="form.image"
            type="url"
            placeholder="https://example.com/image.jpg"
            :class="{ 'error': errors.image }"
          />
          <span v-if="errors.image" class="error-message">{{ errors.image }}</span>
        </div>

        <div class="form-group">
          <label for="password">Mot de passe</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            placeholder="Votre mot de passe"
            :class="{ 'error': errors.password }"
          />
          <span v-if="errors.password" class="error-message">{{ errors.password }}</span>
        </div>

        <div class="form-group">
          <label for="password_confirmation">Confirmer le mot de passe</label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            required
            placeholder="Confirmez votre mot de passe"
            :class="{ 'error': errors.password_confirmation }"
          />
          <span v-if="errors.password_confirmation" class="error-message">{{ errors.password_confirmation }}</span>
        </div>

        <div v-if="error" class="error-alert">
          {{ error }}
        </div>

        <button type="submit" :disabled="loading" class="register-btn">
          <span v-if="loading">Inscription en cours...</span>
          <span v-else>Créer un compte</span>
        </button>
      </form>

      <div class="register-footer">
        <p>
          Déjà un compte ?
          <router-link to="/login" class="link">Se connecter</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(false)
const error = ref('')
const errors = reactive({})

const form = reactive({
  full_name: '',
  email: '',
  phone_number: '',
  address: '',
  image: '',
  password: '',
  password_confirmation: ''
})

const handleRegister = async () => {
  loading.value = true
  error.value = ''
  
  // Réinitialiser les erreurs
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })

  try {
    const result = await authStore.register(form)

    if (result.success) {
      // Rediriger vers la page d'accueil
      router.push('/')
    } else {
      error.value = result.message
      
      // Afficher les erreurs de validation
      if (result.errors) {
        Object.keys(result.errors).forEach(key => {
          errors[key] = result.errors[key][0]
        })
      }
    }
  } catch (err) {
    error.value = 'Une erreur est survenue lors de l\'inscription'
    console.error('Erreur d\'inscription:', err)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.register-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 1rem;
}

.register-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  padding: 2rem;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.register-header {
  text-align: center;
  margin-bottom: 2rem;
}

.register-header h1 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
  font-size: 2rem;
}

.register-header p {
  color: #7f8c8d;
  margin: 0;
}

.register-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 600;
  color: #2c3e50;
  font-size: 0.9rem;
}

.form-group input,
.form-group textarea {
  padding: 0.75rem;
  border: 2px solid #e1e8ed;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s;
  font-family: inherit;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #3498db;
}

.form-group input.error,
.form-group textarea.error {
  border-color: #e74c3c;
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.error-message {
  color: #e74c3c;
  font-size: 0.8rem;
}

.error-alert {
  background: #fdf2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  padding: 0.75rem;
  border-radius: 8px;
  font-size: 0.9rem;
}

.register-btn {
  background: #27ae60;
  color: white;
  border: none;
  padding: 0.75rem;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.3s;
}

.register-btn:hover:not(:disabled) {
  background: #229954;
}

.register-btn:disabled {
  background: #bdc3c7;
  cursor: not-allowed;
}

.register-footer {
  text-align: center;
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid #e1e8ed;
}

.register-footer p {
  color: #7f8c8d;
  margin: 0;
}

.link {
  color: #3498db;
  text-decoration: none;
  font-weight: 600;
}

.link:hover {
  text-decoration: underline;
}

/* Responsive */
@media (max-width: 600px) {
  .register-card {
    padding: 1.5rem;
    max-height: 95vh;
  }
  
  .register-header h1 {
    font-size: 1.5rem;
  }
}
</style>
