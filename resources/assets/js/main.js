import Vue from 'vue';
import VueRouter from 'vue-router';
import store from './store';
import { getRouter } from './router';
import Boot from './Boot.vue';

Vue.use(VueRouter);

window.volumio = window.volumio || {};

var router = getRouter();

// Start the App
router.start(Boot, '#app');