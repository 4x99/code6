require('./bootstrap');

import Vue from 'vue'
import VueRouter from 'vue-router'
import App from '../views/app.vue'

Vue.use(VueRouter);
Vue.config.productionTip = false;

const routes = [];

const router = new VueRouter({
    mode: 'history',
    routes
});

new Vue({
    el: '#app',
    router,
    render: h => h(App)
});
