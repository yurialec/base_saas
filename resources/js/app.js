require('./bootstrap');

import { createApp } from 'vue';
import router from './router';
import App from './App.vue';
import Loading from './components/Loading.vue';
import Pagination from './components/Pagination.vue';
import { registerAlerts } from './plugins/alerts.js';
import { registerAxiosInterceptors } from './plugins/axios_interceptors.js';
import SweetAlertPlugin from './plugins/sweet_alert.js';

const app = createApp(App);

app.component('Loading', Loading);
app.component('Pagination', Pagination);

registerAlerts(app);
registerAxiosInterceptors(router);

app.use(router);
app.use(SweetAlertPlugin);

app.mount('#app');
