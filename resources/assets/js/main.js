import Vue from 'vue';
import VueRouter from 'vue-router';
import store from './store';
import Boot from './Boot.vue';
import { getRouter } from './router';

Vue.use(VueRouter);

window.volumio = window.volumio || {};

let router = getRouter();

const app = new Vue({
  router,
  store,
  ...Boot // Object spread copying everything from App.vue
});

// Start the App
app.$mount('#app');