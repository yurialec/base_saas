import Menus from '../pages/Menus.vue';

export default [
    {
        path: '/menus',
        name: 'menus',
        component: Menus
    },
    {
        path: '/menus/create',
        name: 'menus.create',
        component: () => import('../pages/CreateMenu.vue')
    },
    {
        path: '/menus/edit/:id',
        name: 'menus.edit',
        component: () => import('../pages/EditMenu.vue'),
        props: true
    }
];