<template>
    <section class="page">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h4 mb-1">Meu perfil</h1>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="card border-0 shadow-sm">
                    <div class="card-body bg-white p-4">
                        <form @submit.prevent="update">
                            <div class="row mb-3">
                                <div class="col-12 col-md-6">
                                    <label for="profileName" class="form-label">Nome</label>
                                    <input id="profileName" v-model.trim="user.name" class="form-control" type="text"
                                        autocomplete="name" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="profileEmail" class="form-label">Email</label>
                                    <input id="profileEmail" v-model.trim="user.email" class="form-control" type="email"
                                        autocomplete="email" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12 col-md-6">
                                    <label for="profileRole" class="form-label">Perfil</label>
                                    <input id="profileRole" :value="user.role ? user.role.name : ''"
                                        class="form-control" type="text" readonly>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="profilePassword" class="form-label">Nova senha</label>
                                    <input id="profilePassword" v-model="user.password" class="form-control"
                                        type="password" autocomplete="new-password" minlength="8"
                                        placeholder="Digite uma nova senha">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label">Permissões</label>
                                    <div v-if="user.role && user.role.permissions && user.role.permissions.length"
                                        class="d-flex flex-wrap gap-1">
                                        <span v-for="permission in user.role.permissions" :key="permission.id"
                                            class="badge permission-badge">
                                            {{ permission.name }}
                                        </span>
                                    </div>
                                    <span v-else class="text-muted small">Nenhuma</span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12 col-md-6 offset-md-6">
                                    <label for="profilePasswordConfirmation" class="form-label">Confirmar nova
                                        senha</label>
                                    <input id="profilePasswordConfirmation" v-model="user.password_confirmation"
                                        class="form-control" type="password" autocomplete="new-password" minlength="8"
                                        placeholder="Confirme a nova senha">
                                    <div class="form-text">Deixe os campos de senha vazios para mantê-la.</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2">
                                    <button class="btn btn-primary btn-sm text-white" type="submit"
                                        :disabled="isLoading">
                                        <span v-if="isLoading" class="spinner-border spinner-border-sm me-1"
                                            role="status"></span>
                                        <span>Salvar alterações</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: 'Profile',
    data() {
        return {
            isLoading: false,
            user: {
                name: '',
                email: '',
                role: null,
                password: '',
                password_confirmation: '',
            },
        };
    },
    mounted() {
        this.search();
    },
    methods: {
        search() {
            this.isLoading = true;
            axios.get(`/api/profile`)
                .then(response => {
                    this.user = {
                        ...response.data,
                        password: '',
                        password_confirmation: '',
                    };
                })
                .catch(error => {
                    alertDanger(error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        update() {
            this.isLoading = true;

            const payload = {
                name: this.user.name,
                email: this.user.email,
                password: this.user.password || null,
                password_confirmation: this.user.password_confirmation || null,
            };

            axios.put(`/api/profile/update`, payload)
                .then(response => {
                    this.user = {
                        ...response.data.user,
                        password: '',
                        password_confirmation: '',
                    };
                    alertSuccess(response.data.message);
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

.profile-avatar {
    width: 5rem;
    height: 5rem;
}

.permission-badge {
    max-width: 100%;
    overflow: hidden;
    color: var(--bs-primary-text-emphasis);
    background-color: var(--bs-primary-bg-subtle);
    font-weight: 500;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
