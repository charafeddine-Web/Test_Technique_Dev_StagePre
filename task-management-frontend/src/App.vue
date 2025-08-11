<template>
  <div id="app">
    <!-- Navigation -->
    <nav v-if="authStore.isAuthenticated" class="navbar">
      <div class="nav-container">
        <div class="nav-brand">
          <router-link to="/" class="nav-link">Task Manager</router-link>
        </div>
        
        <div class="nav-menu">
          <router-link to="/" class="nav-link">Accueil</router-link>
          <router-link to="/tasks" class="nav-link">Tâches</router-link>
          <router-link to="/notifications" class="nav-link">
            Notifications
            <span v-if="notificationsStore.unreadCount > 0" class="badge">
              {{ notificationsStore.unreadCount }}
            </span>
          </router-link>
          <router-link to="/profile" class="nav-link">Profil</router-link>
          <button @click="logout" class="nav-link logout-btn">Déconnexion</button>
        </div>
      </div>
    </nav>

    <!-- Contenu principal -->
    <main class="main-content">
      <router-view />
    </main>

    <!-- Toast notifications -->
    <div v-if="toast.show" class="toast" :class="toast.type">
      {{ toast.message }}
      <button @click="hideToast" class="toast-close">&times;</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationsStore } from '@/stores/notifications'
import { initializeEcho, setupEchoListeners, disconnectEcho } from '@/services/echo'

const router = useRouter()
const authStore = useAuthStore()
const notificationsStore = useNotificationsStore()

// Toast state
const toast = ref({
  show: false,
  message: '',
  type: 'info'
})

// Méthodes
const logout = async () => {
  disconnectEcho()
  authStore.logout()
  router.push('/login')
}

const showToast = (message, type = 'info') => {
  toast.value = {
    show: true,
    message,
    type
  }
  
  // Auto-hide after 5 seconds
  setTimeout(() => {
    hideToast()
  }, 5000)
}

const hideToast = () => {
  toast.value.show = false
}

// Watchers
watch(() => authStore.isAuthenticated, (isAuthenticated) => {
  if (isAuthenticated) {
    // Initialiser Echo quand l'utilisateur se connecte
    initializeEcho()
    setupEchoListeners()
    
    // Charger les notifications
    notificationsStore.fetchNotifications()
    notificationsStore.fetchUnreadCount()
  } else {
    // Déconnecter Echo quand l'utilisateur se déconnecte
    disconnectEcho()
  }
})

// Lifecycle
onMounted(async () => {
  // Charger les notifications si l'utilisateur est connecté
  if (authStore.isAuthenticated) {
    await notificationsStore.fetchNotifications()
    await notificationsStore.fetchUnreadCount()
  }
})

// Exposer les méthodes globalement
window.showToast = showToast
</script>

<style scoped>
#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.navbar {
  background: #2c3e50;
  padding: 1rem 0;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.nav-brand .nav-link {
  font-size: 1.5rem;
  font-weight: bold;
  color: #ecf0f1;
  text-decoration: none;
}

.nav-menu {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.nav-link {
  color: #ecf0f1;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  transition: background-color 0.3s;
  position: relative;
}

.nav-link:hover {
  background-color: #34495e;
}

.nav-link.router-link-active {
  background-color: #3498db;
}

.logout-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1rem;
}

.badge {
  background: #e74c3c;
  color: white;
  border-radius: 50%;
  padding: 0.2rem 0.5rem;
  font-size: 0.8rem;
  position: absolute;
  top: -5px;
  right: -5px;
}

.main-content {
  flex: 1;
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
}

.toast {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 1rem 1.5rem;
  border-radius: 4px;
  color: white;
  z-index: 1000;
  display: flex;
  align-items: center;
  gap: 1rem;
  min-width: 300px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.toast.success {
  background-color: #27ae60;
}

.toast.error {
  background-color: #e74c3c;
}

.toast.info {
  background-color: #3498db;
}

.toast.warning {
  background-color: #f39c12;
}

.toast-close {
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  margin-left: auto;
}

.toast-close:hover {
  opacity: 0.8;
}

/* Responsive */
@media (max-width: 768px) {
  .nav-container {
    flex-direction: column;
    gap: 1rem;
  }
  
  .nav-menu {
    flex-wrap: wrap;
    justify-content: center;
  }
  
  .main-content {
    padding: 1rem;
  }
  
  .toast {
    left: 20px;
    right: 20px;
    min-width: auto;
  }
}
</style>
