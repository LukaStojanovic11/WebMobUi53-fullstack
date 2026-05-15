// On importe bootstrap.js en premier — configure le CSRF token et l'URL de base
import './bootstrap';

import { createApp } from 'vue';
import AppPollForm from './AppPollForm.vue';

// On monte l'application Vue sur l'élément HTML avec l'id "app"
createApp(AppPollForm).mount('#app');
