<template>
    <div>
        <AppAlert />
        <app-header @toggle-sidebar="toggleSidebar" @logout="logout"></app-header>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <app-sidebar @logout="logout"></app-sidebar>
            </div>
            <div id="layoutSidenav_content">
                <main class="h-100" style="background-color: #f7f7f7;">
                    <div class="container-fluid px-4 py-4">
                        <router-view></router-view>
                    </div>
                </main>
                <app-footer></app-footer>
            </div>
        </div>
    </div>
</template>

<script>
import AppFooter from './components/AppFooter.vue';
import AppHeader from './components/AppHeader.vue';
import AppSidebar from './components/AppSidebar.vue';
import AppAlert from './components/Alert.vue';

export default {
    name: 'App',
    components: {
        AppAlert,
        AppFooter,
        AppHeader,
        AppSidebar
    },
    data() {
        return {
            isSidebarToggled: false
        }
    },
    watch: {
        isSidebarToggled(value) {
            document.body.classList.toggle('sb-sidenav-toggled', value);
        }
    },
    mounted() {
        document.body.classList.add('sb-nav-fixed');
    },
    beforeUnmount() {
        document.body.classList.remove('sb-nav-fixed', 'sb-sidenav-toggled');
    },
    methods: {
        toggleSidebar() {
            this.isSidebarToggled = !this.isSidebarToggled;
        },
        logout() {
            axios.post('/logout')
                .then(() => {
                    window.location.href = '/login';
                })
                .catch(error => {
                    console.error('Logout failed:', error);
                });
        }
    }
}
</script>
