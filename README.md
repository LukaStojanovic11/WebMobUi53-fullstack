# WebMobUi53 — Application de sondage

Application web fullstack Laravel + Vue.js permettant de créer, gérer et partager des sondages.

## Technologies

- **Backend** : Laravel 12
- **Frontend** : Vue.js 3 + Tailwind CSS
- **Base de données** : SQLite
- **Auth** : Laravel Sanctum (cookie de session)

## Installation

### 1. Cloner le projet

    git clone https://github.com/LukaStojanovic11/WebMobUi53-fullstack.git
    cd WebMobUi53-fullstack

### 2. Installer les dépendances

    composer install
    npm install

### 3. Configurer l'environnement

    cp .env.example .env
    php artisan key:generate

Modifier .env :

    APP_URL=http://localhost:8000
    SANCTUM_STATEFUL_DOMAINS=localhost:8000
    SESSION_DOMAIN=localhost
    SESSION_DRIVER=database

### 4. Créer la base de données

    php artisan migrate
    php artisan db:seed

### 5. Lancer l'application

Dans deux terminaux séparés :

    php artisan serve

    npm run dev

L'application est accessible sur http://localhost:8000

## Comptes de test

Après le seeder :
- Email : john.doe@example.com / Mot de passe : password
- Email : jane.doe@example.com / Mot de passe : password

## Fonctionnalités

- Créer, modifier et supprimer des sondages
- Configurer les options : choix simple ou multiple, résultats publics, durée
- Lancer un sondage depuis le formulaire ou plus tard depuis le dashboard
- Partager un sondage via un lien contenant un token unique
- Voter sur un sondage via le lien de partage
- Voir les résultats en temps réel (polling toutes les 5 secondes)
- Accès anonyme aux résultats si les résultats sont publics

## Architecture frontend

Le frontend est composé de 3 applications Vue.js distinctes :

- poll-dashboard.js → /polls/dashboard (liste des sondages)
- poll-form.js → /polls/create et /polls/{id}/edit (formulaire)
- poll-vote.js → /polls/{token} (page de vote)

Chaque app est montée sur un div id="app" dans sa vue Blade respective.

## API JSON

Toutes les routes API sont préfixées par /api/v1/

- GET    /api/v1/polls              → Liste des sondages (auth)
- POST   /api/v1/polls              → Créer un sondage (auth)
- PUT    /api/v1/polls/{id}         → Modifier un sondage (auth)
- DELETE /api/v1/polls/{id}         → Supprimer un sondage (auth)
- GET    /api/v1/polls/{token}      → Afficher un sondage (public)
- POST   /api/v1/polls/{token}/vote → Voter (auth)
- POST   /api/v1/polls/{id}/options           → Ajouter une option (auth)
- PUT    /api/v1/polls/{id}/options/{oid}     → Modifier une option (auth)
- DELETE /api/v1/polls/{id}/options/{oid}     → Supprimer une option (auth)
