import { createRouter, createWebHistory } from 'vue-router';

import adminRoutes from './admin';
import menuRoutes from './menus';
import roleRoutes from './roles';
import permissionRoutes from './permissions';
import usersRoutes from './users';

const publicRoutes = ['login', 'register'];
const firstPathSegment = window.location.pathname.split('/')[1];

const base = publicRoutes.includes(firstPathSegment) ? '/' : `/${firstPathSegment}/`;

const router = createRouter({
    history: createWebHistory(base),

    routes: [
        ...adminRoutes,
        ...menuRoutes,
        ...roleRoutes,
        ...permissionRoutes,
        ...usersRoutes
    ]
});

export default router;