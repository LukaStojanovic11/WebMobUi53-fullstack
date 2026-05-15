// On importe bootstrap.js en premier — il configure le CSRF token et l'URL de base de l'API
import './bootstrap';

// On importe Vue et notre composant principal
import { createApp } from 'vue';
import AppPollDashboard from './AppPollDashboard.vue';

// On monte l'application Vue sur l'élément HTML avec l'id "app"
createApp(AppPollDashboard).mount('#app');
