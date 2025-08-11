import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)
  const isAuthenticated = ref(false)

  // Getters
  const getUser = computed(() => user.value)
  const getToken = computed(() => token.value)
  const getIsAuthenticated = computed(() => isAuthenticated.value)

  // Actions
  const login = async (credentials) => {
    try {
      const response = await axios.post('http://localhost:8000/api/auth/login', credentials)
      
      if (response.data.success) {
        const { user: userData, token: tokenData } = response.data.data
        
        user.value = userData
        token.value = tokenData
        isAuthenticated.value = true
        
        // Sauvegarder le token dans localStorage
        localStorage.setItem('token', tokenData)
        
        // Configurer axios avec le token
        axios.defaults.headers.common['Authorization'] = `Bearer ${tokenData}`
        
        return { success: true, data: response.data.data }
      }
    } catch (error) {
      console.error('Erreur de connexion:', error)
      return { 
        success: false, 
        message: error.response?.data?.message || 'Erreur de connexion' 
      }
    }
  }

  const register = async (userData) => {
    try {
      const response = await axios.post('http://localhost:8000/api/auth/register', userData)
      
      if (response.data.success) {
        const { user: userData, token: tokenData } = response.data.data
        
        user.value = userData
        token.value = tokenData
        isAuthenticated.value = true
        
        // Sauvegarder le token dans localStorage
        localStorage.setItem('token', tokenData)
        
        // Configurer axios avec le token
        axios.defaults.headers.common['Authorization'] = `Bearer ${tokenData}`
        
        return { success: true, data: response.data.data }
      }
    } catch (error) {
      console.error('Erreur d\'inscription:', error)
      return { 
        success: false, 
        message: error.response?.data?.message || 'Erreur d\'inscription',
        errors: error.response?.data?.errors || {}
      }
    }
  }

  const logout = () => {
    user.value = null
    token.value = null
    isAuthenticated.value = false
    
    // Supprimer le token du localStorage
    localStorage.removeItem('token')
    
    // Supprimer le header Authorization d'axios
    delete axios.defaults.headers.common['Authorization']
  }

  const checkAuth = async () => {
    if (!token.value) {
      return false
    }

    try {
      // Configurer axios avec le token
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      
      const response = await axios.get('http://localhost:8000/api/auth/me')
      
      if (response.data.success) {
        user.value = response.data.data.user
        isAuthenticated.value = true
        return true
      }
    } catch (error) {
      console.error('Erreur de vérification d\'authentification:', error)
      logout()
      return false
    }
  }

  const initializeAuth = async () => {
    if (token.value) {
      await checkAuth()
    }
  }

  return {
    // State
    user,
    token,
    isAuthenticated,
    
    // Getters
    getUser,
    getToken,
    getIsAuthenticated,
    
    // Actions
    login,
    register,
    logout,
    checkAuth,
    initializeAuth
  }
})
