<template>
    <section class="page">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <h1 class="h4 mb-0">Editar usuário</h1>
            <router-link class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2"
                :to="{ name: 'users' }">
                <i class="bi bi-arrow-left"></i>
                <span>Voltar</span>
            </router-link>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-12 col-xl-4">
                <Loading :loading="isLoading" />
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body bg-white">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-12">
                                <label class="form-label">Nome</label>
                                <input v-model="user.name" class="form-control" type="text" required>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-12">
                                    <label class="form-label">Email</label>
                                    <input v-model="user.email" class="form-control" type="email" required>
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-12">
                                    <label class="form-label">Perfil</label>
                                    <select v-model="user.role_id" class="form-select" required>
                                        <option value="">Selecione um perfil</option>
                                        <option v-for="role in roles" :key="role.id" :value="role.id">
                                            {{ role.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div v-show="changePass" class="col-12 col-md-12">
                                    <label class="form-label">Senha</label>
                                    <input v-model="new_password" class="form-control" type="password"
                                        :required="changePass">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <div class="d-flex justify-content-end">
                                <button
                                    class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2 me-2"
                                    @click="btnChangePassword">
                                    Alterar senha
                                </button>
                            </div>
                            <button class="btn btn-primary btn-sm text-white" type="submit" :disabled="isLoading"
                                @click="update">
                                <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status"></span>
                                <span v-else>Salvar</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: 'EditUser',
    props: {
        id: {
            type: [String, Number],
            required: true
        }
    },
    data() {
        return {
            isLoading: false,
            user: {
                name: '',
                email: '',
                role_id: '',
            },
            roles: [],
            new_password: '',
            changePass: false,
        };
    },
    mounted() {
        this.find();
        this.searchRoles();
    },
    methods: {
        find() {
            this.isLoading = true;

            axios.get(`/api/users/find/${this.id}`)
                .then(response => {
                    this.user = response.data;
                })
                .catch(error => {
                    alertDanger(error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        searchRoles() {
            this.isLoading = true;
            axios.get(`/api/users/roles/list`)
                .then(response => {
                    this.roles = response.data;
                })
                .catch(error => {
                    alertDanger(error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        update() {

            if (this.changePass) {
                this.user.password = this.new_password;
            } else {
                delete this.user.password;
            }

            this.isLoading = true;
            axios.put(`/api/users/update/${this.id}`, this.user)
                .then(response => {
                    this.$router.push({ name: 'users' });
                })
                .catch(error => {
                    alertDanger(error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        btnChangePassword() {
            if (!this.changePass) {
                this.changePass = true;
            } else {
                this.changePass = false;
            }
        }
    }
};
</script>

<style scoped>
.page .card {
    border-radius: 0.5rem;
}
</style>
