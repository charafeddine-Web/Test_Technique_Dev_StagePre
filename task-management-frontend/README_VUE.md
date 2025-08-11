# Application Vue.js - Task Management System

## Vue d'ensemble

Cette application Vue.js est le frontend pour le système de gestion de tâches avec notifications en temps réel. Elle utilise Vue 3, Pinia pour la gestion d'état, Vue Router pour la navigation, et Laravel Echo avec Pusher pour les notifications en temps réel.

## Technologies utilisées

- **Vue.js 3** - Framework JavaScript progressif
- **Pinia** - Gestion d'état moderne pour Vue
- **Vue Router 4** - Routage côté client
- **Axios** - Client HTTP pour les appels API
- **Laravel Echo** - Bibliothèque pour les événements en temps réel
- **Pusher-js** - Client JavaScript pour Pusher

## Structure du projet

```
src/
├── stores/                 # Stores Pinia
│   ├── auth.js            # Gestion de l'authentification
│   ├── tasks.js           # Gestion des tâches
│   └── notifications.js   # Gestion des notifications
├── services/              # Services
│   └── echo.js           # Configuration Laravel Echo
├── views/                 # Pages de l'application
│   ├── Home.vue          # Page d'accueil
│   ├── Login.vue         # Page de connexion
│   ├── Register.vue      # Page d'inscription
│   ├── Tasks.vue         # Page des tâches
│   ├── TaskDetail.vue    # Détail d'une tâche
│   ├── Notifications.vue # Page des notifications
│   └── Profile.vue       # Page de profil
├── router/               # Configuration du router
│   └── index.js         # Routes de l'application
├── App.vue              # Composant principal
└── main.js              # Point d'entrée de l'application
```

## Installation et configuration

### 1. Installer les dépendances

```bash
cd task-management-frontend
npm install
```

### 2. Configuration Pusher

Modifiez le fichier `src/services/echo.js` avec vos clés Pusher :

```javascript
echo = new Echo({
  broadcaster: 'pusher',
  key: 'your-pusher-key',        // Votre clé Pusher
  cluster: 'your-cluster',       // Votre cluster Pusher
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
```

### 3. Configuration de l'API

L'URL de l'API est configurée dans `src/main.js` :

```javascript
axios.defaults.baseURL = 'http://localhost:8000/api'
```

Assurez-vous que votre serveur Laravel fonctionne sur `http://localhost:8000`.

### 4. Démarrer l'application

```bash
npm run dev
```

L'application sera accessible sur `http://localhost:5173` (ou un autre port si 5173 est occupé).

## Fonctionnalités

### 🔐 Authentification

- **Inscription** : Création de compte avec nom complet, email, téléphone, adresse, image et mot de passe
- **Connexion** : Authentification avec email et mot de passe
- **Gestion des tokens JWT** : Stockage automatique et utilisation des tokens
- **Protection des routes** : Navigation automatique vers la connexion si non authentifié

### 📋 Gestion des tâches

- **Liste des tâches** : Affichage de toutes les tâches de l'utilisateur
- **Création de tâches** : Formulaire modal pour créer de nouvelles tâches
- **Modification de tâches** : Édition des tâches existantes
- **Suppression de tâches** : Suppression avec confirmation
- **Filtrage par statut** : Affichage des tâches par statut (En attente, En cours, Terminé)

### 🔔 Notifications en temps réel

- **Notifications instantanées** : Réception immédiate des notifications lors de la création de tâches
- **Gestion des notifications** : Marquer comme lues, supprimer
- **Compteur de notifications** : Affichage du nombre de notifications non lues
- **Historique** : Consultation de toutes les notifications

### 📊 Tableau de bord

- **Statistiques** : Nombre de tâches par statut et notifications non lues
- **Actions rapides** : Accès direct aux fonctionnalités principales
- **Tâches récentes** : Affichage des 6 tâches les plus récentes

## Architecture

### Stores Pinia

#### AuthStore (`stores/auth.js`)
Gère l'état d'authentification :
- Connexion/déconnexion
- Inscription
- Vérification de l'authentification
- Gestion des tokens JWT

#### TasksStore (`stores/tasks.js`)
Gère les tâches :
- Récupération des tâches
- Création/modification/suppression
- Filtrage par statut
- Gestion des erreurs

#### NotificationsStore (`stores/notifications.js`)
Gère les notifications :
- Récupération des notifications
- Marquer comme lues
- Suppression
- Compteur de notifications non lues

### Service Echo (`services/echo.js`)

Configure Laravel Echo pour les notifications en temps réel :
- Initialisation de la connexion Pusher
- Écoute des événements de création de tâches
- Écoute des notifications Laravel
- Gestion de la déconnexion

### Router (`router/index.js`)

Configuration des routes avec protection :
- Routes publiques (login, register)
- Routes protégées (toutes les autres)
- Navigation automatique selon l'état d'authentification

## Utilisation

### 1. Inscription/Connexion

1. Accédez à l'application
2. Cliquez sur "Créer un compte" ou "Se connecter"
3. Remplissez le formulaire
4. Vous serez automatiquement redirigé vers le tableau de bord

### 2. Création de tâches

1. Sur la page d'accueil, cliquez sur "Nouvelle tâche"
2. Remplissez le formulaire (titre, description, statut)
3. Cliquez sur "Créer la tâche"
4. Une notification apparaîtra instantanément

### 3. Gestion des notifications

1. Cliquez sur "Notifications" dans la navigation
2. Consultez vos notifications
3. Marquez-les comme lues ou supprimez-les
4. Les notifications en temps réel apparaîtront automatiquement

### 4. Gestion des tâches

1. Cliquez sur "Tâches" dans la navigation
2. Consultez toutes vos tâches
3. Modifiez ou supprimez les tâches selon vos besoins

## Configuration avancée

### Variables d'environnement

Créez un fichier `.env` à la racine du projet :

```env
VITE_API_URL=http://localhost:8000/api
VITE_PUSHER_KEY=your-pusher-key
VITE_PUSHER_CLUSTER=your-cluster
```

### Personnalisation des styles

Les styles sont organisés par composant. Vous pouvez modifier :
- `src/App.vue` : Styles globaux et navigation
- `src/views/*.vue` : Styles spécifiques à chaque page
- `src/components/*.vue` : Styles des composants

### Ajout de nouvelles fonctionnalités

1. **Nouveau store** : Créez un fichier dans `src/stores/`
2. **Nouvelle page** : Créez un fichier dans `src/views/`
3. **Nouvelle route** : Ajoutez la route dans `src/router/index.js`
4. **Nouveau service** : Créez un fichier dans `src/services/`

## Déploiement

### Build de production

```bash
npm run build
```

### Variables d'environnement de production

```env
VITE_API_URL=https://your-api-domain.com/api
VITE_PUSHER_KEY=your-production-pusher-key
VITE_PUSHER_CLUSTER=your-production-cluster
```

### Serveur de production

```bash
npm run preview
```

## Dépannage

### Problèmes courants

1. **Erreur de connexion Pusher**
   - Vérifiez vos clés Pusher dans `src/services/echo.js`
   - Assurez-vous que votre cluster est correct

2. **Erreur d'API**
   - Vérifiez que votre serveur Laravel fonctionne
   - Vérifiez l'URL de l'API dans `src/main.js`

3. **Notifications ne s'affichent pas**
   - Vérifiez la configuration Echo
   - Vérifiez les logs de la console du navigateur

4. **Problèmes d'authentification**
   - Vérifiez que les tokens JWT sont corrects
   - Vérifiez la configuration CORS côté Laravel

### Logs et débogage

- Ouvrez les outils de développement du navigateur
- Consultez la console pour les erreurs JavaScript
- Vérifiez l'onglet Network pour les appels API
- Utilisez Vue DevTools pour inspecter l'état des stores

## Extensions possibles

### Fonctionnalités avancées

- **Filtres et recherche** : Recherche dans les tâches
- **Tri** : Tri par date, statut, priorité
- **Pagination** : Pagination des tâches et notifications
- **Export** : Export des tâches en PDF/Excel
- **Thèmes** : Mode sombre/clair
- **Notifications push** : Notifications du navigateur
- **Mode hors ligne** : Fonctionnement sans connexion

### Intégrations

- **Calendrier** : Intégration avec Google Calendar
- **Email** : Notifications par email
- **SMS** : Notifications par SMS
- **Slack** : Intégration avec Slack
- **Trello** : Synchronisation avec Trello

## Support

Pour toute question ou problème :
1. Consultez la documentation de l'API Laravel
2. Vérifiez les logs de la console
3. Consultez la documentation Vue.js et Pinia
4. Contactez l'équipe de développement
