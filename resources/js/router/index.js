import Vue from 'vue';
import VueRouter from 'vue-router';
import Layout from '../views/layout/Index';
import Home from '../views/home/Index';
import CodeLeak from '../views/codeLeak/Index';
import ConfigToken from '../views/configToken/Index';
import ConfigJob from '../views/configJob/Index';
import ConfigWhitelist from '../views/configWhitelist/Index';

Vue.use(VueRouter);

const routes = [
    {
        path: '/',
        component: Layout,
        redirect: '/home',
        children: [
            {
                path: 'home',
                component: Home,
            },
            {
                path: 'codeLeak',
                component: CodeLeak,
            },
            {
                path: 'configToken',
                component: ConfigToken,
            },
            {
                path: 'configJob',
                component: ConfigJob,
            },
            {
                path: 'configWhitelist',
                component: ConfigWhitelist,
            }
        ],
    }
];

const router = new VueRouter({
    mode: 'history',
    routes,
});

export default router;
