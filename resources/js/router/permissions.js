import Permissions from '../pages/Permissions.vue';

export default [
    {
        path: '/permissions',
        name: 'permissions',
        component: Permissions,
        meta: { permission: 'permissions' }
    },
    {
        path: '/permissions/create',
        name: 'permissions.create',
        component: () => import('../pages/CreatePermission.vue'),
        meta: { permission: 'permissions' }
    },
    {
        path: '/permissions/edit/:id',
        name: 'permissions.edit',
        component: () => import('../pages/EditPermission.vue'),
        props: true,
        meta: { permission: 'permissions' }
    }
];
