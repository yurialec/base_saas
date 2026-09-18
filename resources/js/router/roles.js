import Roles from '../pages/Roles.vue';

export default [
    {
        path: '/roles',
        name: 'roles',
        component: Roles,
        meta: { permission: 'roles' }
    },
    {
        path: '/roles/create',
        name: 'roles.create',
        component: () => import('../pages/CreateRole.vue'),
        meta: { permission: 'roles' }
    },
    {
        path: '/roles/edit/:id',
        name: 'roles.edit',
        component: () => import('../pages/EditRole.vue'),
        props: true,
        meta: { permission: 'roles' }
    }
];
