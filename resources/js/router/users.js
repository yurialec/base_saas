import Users from '../pages/Users.vue';

export default [
    {
        path: '/users',
        name: 'users',
        component: Users,
        meta: { permission: 'users' }
    },
    {
        path: '/users/create',
        name: 'users.create',
        component: () => import('../pages/CreateUser.vue'),
        meta: { permission: 'users' }
    },
    {
        path: '/users/edit/:id',
        name: 'users.edit',
        component: () => import('../pages/EditUser.vue'),
        props: true,
        meta: { permission: 'users' }
    }
];
