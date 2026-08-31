import Dashboard from '../pages/Dashboard.vue';
import Profile from '../pages/Profile.vue';

export default [
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard
    },
    {
        path: '/',
        redirect: { name: 'dashboard' }
    },
    {
        path: '/profile',
        name: 'profile',
        component: Profile
    },
];