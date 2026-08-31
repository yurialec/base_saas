<template>
    <nav class="sb-topnav navbar navbar-expand navbar-light bg-light shadow fixed-top">
        <div class="container-fluid p-0">
            <router-link class="navbar-brand ps-3" to="/">
                Administrativo
            </router-link>

            <button id="sidebarToggle" class="btn btn-link btn-sm me-4 me-lg-0" type="button"
                aria-label="Alternar menu" @click="$emit('toggle-sidebar')">
                <i class="bi bi-list fs-3 text-dark"></i>
            </button>

            <ul class="navbar-nav ms-auto me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <button id="navbarDropdown" class="nav-link dropdown-toggle border-0 bg-transparent" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false" aria-label="Abrir menu do usuário">
                        <i class="bi bi-person-circle fs-3"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end user-menu" aria-labelledby="navbarDropdown">
                        <li>
                            <router-link class="dropdown-item" :to="{ name: 'profile' }">
                                <i class="bi bi-person fs-5 me-2"></i>Meu Perfil
                            </router-link>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <button class="dropdown-item" type="button" @click="$emit('logout')">
                                <i class="bi bi-power fs-5 me-2"></i>Sair
                            </button>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</template>

<script>
export default {
    name: 'AppHeader'
}
</script>

<style scoped>
.user-menu .dropdown-item:hover,
.user-menu .dropdown-item:focus {
    background-color: rgba(var(--bs-primary-rgb), 0.1);
}
</style>
