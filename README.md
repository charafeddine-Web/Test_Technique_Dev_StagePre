# Task Management System

Un système complet de gestion de tâches avec authentification JWT et notifications en temps réel, composé d'une API Laravel et d'un frontend Vue.js.

## 🚀 Fonctionnalités

### Backend (Laravel)
- ✅ **Authentification JWT** - Inscription, connexion, gestion des tokens
- ✅ **Gestion des tâches** - CRUD complet avec validation
- ✅ **Notifications en temps réel** - Via Laravel Events et Pusher
- ✅ **Architecture Repository** - Séparation des responsabilités


### Frontend (Vue.js)
- ✅ **Interface moderne** - Design responsive et intuitif
- ✅ **Gestion d'état Pinia** - État centralisé et réactif
- ✅ **Notifications temps réel** - Intégration Laravel Echo + Pusher
- ✅ **Navigation Vue Router** - Routage côté client avec protection
- ✅ **Appels API Axios** - Gestion automatique des tokens JWT

## 📁 Structure du projet

```
project/
├── laravel-api/                 # Backend Laravel
│   ├── app/
│   │   ├── Http/Controllers/    # Contrôleurs API
│   │   ├── Services/           # Couche métier
│   │   ├── Repositories/       # Pattern Repository
│   │   ├── Events/            # Événements temps réel
│   │   ├── Notifications/     # Notifications Laravel
│   │   └── Models/            # Modèles Eloquent
│   ├── routes/api.php         # Routes API
│
└── task-management-frontend/   # Frontend Vue.js
    ├── src/
    │   ├── stores/            # Stores Pinia
    │   ├── views/             # Pages Vue
    │   ├── services/          # Services (Echo, etc.)
    │   └── router/            # Configuration Vue Router
    └── package.json           # Dépendances Vue.js
```

## 🛠️ Installation

### Prérequis
- PHP 8.1+
- Composer
- Node.js 16+
- MySQL
- Compte Pusher (pour les notifications temps réel)

### 1. Backend Laravel

```bash
# Cloner le projet
git clone https://github.com/charafeddine-Web/Test_Technique_Dev_StagePre.git
cd laravel-api

# Installer les dépendances
composer install

# Configuration
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# Configuration base de données
# Modifier .env avec vos paramètres DB

# Migrations et seeders
php artisan migrate
php artisan db:seed

# Configuration Pusher
# Ajouter vos clés Pusher dans .env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster

# Démarrer le serveur
php artisan serve
```

### 2. Frontend Vue.js

```bash
cd task-management-frontend

# Installer les dépendances
npm install

# Configuration
cp env.example .env
# Modifier .env avec vos clés Pusher

# Démarrer le serveur de développement
npm run dev
```

## 🔧 Configuration

### Variables d'environnement Laravel (.env)

```env
# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=root
DB_PASSWORD=

# JWT
JWT_SECRET=your_jwt_secret
JWT_TTL=60

# Pusher
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster
```

### Variables d'environnement Vue.js (.env)

```env
VITE_API_URL=http://localhost:8000/api
VITE_PUSHER_KEY=your_pusher_key
VITE_PUSHER_CLUSTER=your_cluster
```

## 📖 Utilisation

### 1. Inscription/Connexion
- Accédez à `http://localhost:5173`
- Créez un compte ou connectez-vous
- Vous serez automatiquement redirigé vers le tableau de bord

### 2. Gestion des tâches
- **Créer une tâche** : Cliquez sur "Nouvelle tâche" depuis le tableau de bord
- **Voir toutes les tâches** : Accédez à la page "Tâches"
- **Modifier/Supprimer** : Utilisez les boutons d'action sur chaque tâche
- **Filtrer/Rechercher** : Utilisez les filtres sur la page des tâches

### 3. Notifications temps réel
- Les notifications apparaissent automatiquement lors de la création de tâches
- Consultez l'historique dans la page "Notifications"
- Marquez les notifications comme lues ou supprimez-les



## 📚 Documentation

## 🔌 API Endpoints

### Authentification
- `POST /api/auth/register` - Inscription
- `POST /api/auth/login` - Connexion
- `GET /api/auth/me` - Profil utilisateur

### Tâches
- `GET /api/tasks` - Liste des tâches
- `POST /api/tasks` - Créer une tâche
- `GET /api/tasks/{id}` - Détail d'une tâche
- `PUT /api/tasks/{id}` - Modifier une tâche
- `DELETE /api/tasks/{id}` - Supprimer une tâche

### Notifications
- `GET /api/notifications` - Liste des notifications
- `PATCH /api/notifications/{id}/read` - Marquer comme lue
- `PATCH /api/notifications/mark-all-read` - Marquer tout comme lu
- `DELETE /api/notifications/{id}` - Supprimer une notification



---

**Développé avec charaf eddine tbibzat  en utilisant Laravel et Vue.js**
