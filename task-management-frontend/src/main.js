import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import axios from 'axios'

// Configuration axios
axios.defaults.baseURL = 'http://localhost:8000/api'
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

// Intercepteur pour ajouter le token automatiquement
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Intercepteur pour gérer les erreurs d'authentification
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expiré ou invalide
      localStorage.removeItem('token')
      router.push('/login')
    }
    return Promise.reject(error)
  }
)

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// Initialiser l'authentification et Echo après la création de l'app
const { useAuthStore } = await import('./stores/auth')
const { initializeEcho, setupEchoListeners } = await import('./services/echo')

app.mount('#app')

// Initialiser l'authentification
const authStore = useAuthStore()
await authStore.initializeAuth()

// Initialiser Echo si l'utilisateur est connecté
if (authStore.isAuthenticated) {
  initializeEcho()
  setupEchoListeners()
}
