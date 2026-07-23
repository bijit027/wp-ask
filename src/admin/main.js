import { createApp } from 'vue';
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import App from './App.vue';
import router from './router';

const mountEl = document.getElementById('pollquest-admin-app');

if (mountEl) {
  const app = createApp(App);
  app.use(ElementPlus);
  app.use(router);
  app.mount('#pollquest-admin-app');
}
