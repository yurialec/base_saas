<template>
    <section class="page">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <h1 class="h4 mb-0">Cadastrar usuário</h1>
            <router-link class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2"
                :to="{ name: 'users' }">
                <i class="bi bi-arrow-left"></i>
                <span>Voltar</span>
            </router-link>
        </div>

        <form class="row g-4 justify-content-center" @submit.prevent="store">
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h2 class="h6 mb-0">Dados do usuário</h2>
                    </div>
                    <div class="card-body bg-white p-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="userName" class="form-label">Nome completo</label>
                                <input id="userName" v-model.trim="user.name" class="form-control" type="text"
                                    autocomplete="name" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="userEmail" class="form-label">E-mail</label>
                                <input id="userEmail" v-model.trim="user.email" class="form-control" type="email"
                                    autocomplete="email" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="userPassword" class="form-label">Senha</label>
                                <input id="userPassword" v-model="user.password" class="form-control" type="password"
                                    autocomplete="new-password" minlength="8" required>
                                <div class="form-text">Mínimo de 8 caracteres.</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="userRole" class="form-label">Perfil</label>
                                <select id="userRole" v-model="user.role_id" class="form-select" required>
                                    <option value="">Selecione um perfil</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.id">
                                        {{ role.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button class="btn btn-primary btn-sm text-white" type="submit" :disabled="isLoading">
                                <span v-if="isLoading" class="spinner-border spinner-border-sm me-1" role="status"></span>
                                <i v-else class="bi bi-check-lg me-1"></i>
                                <span>Cadastrar</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
</template>

<script>
export default {
    name: 'CreateUser',
    data() {
        return {
            isLoading: false,
            user: {
                name: '',
                email: '',
                password: '',
                role_id: ''
            },
            roles: []
        };
    },
    mounted() {
        this.searchRoles();
    },
    methods: {
        searchRoles() {
            this.isLoading = true;

            axios.get('/api/roles/dropdown-list')
                .then(response => {
                    this.roles = Array.isArray(response.data) ? response.data : [];
                })
                .catch(error => {
                    alertDanger(error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        store() {
            this.isLoading = true;

            axios.post('/api/users/store', this.user)
                .then(response => {
                    this.$router.push({ name: 'users' });
                })
                .catch(error => {
                    alertDanger(error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        }
    }
};
</script>

<style scoped>
.page .card {
    border-radius: 0.5rem;
}

.page .card-header {
    padding: 1.25rem 1.5rem 0;
}
</style>
