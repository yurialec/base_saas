<template>
    <section class="page">
        <h1 class="h4 mb-3">Minha agenda</h1>
        <p class="text-muted">Cadastre seus horários e acompanhe a sincronização com o Google Calendar.</p>

        <div v-if="notice" class="alert" :class="noticeType" role="status">{{ notice }}</div>
        <div v-if="loadError" class="alert alert-danger" role="alert">{{ loadError }}</div>

        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5">Novo agendamento</h2>
                        <p v-if="timezone" class="small text-muted">
                            Fuso: {{ timezone }} · Duração: {{ duration }} minutos
                        </p>
                        <form @submit.prevent="schedule">
                            <fieldset :disabled="saving || loading || !timezone">
                                <div class="mb-3">
                                    <label for="agenda-data" class="form-label">Data</label>
                                    <input id="agenda-data" v-model="form.data" type="date" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="agenda-hora" class="form-label">Hora</label>
                                    <input id="agenda-hora" v-model="form.hora" type="time" step="60" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="agenda-comentario" class="form-label">Comentário</label>
                                    <textarea id="agenda-comentario" v-model="form.comentario" class="form-control"
                                        rows="4" maxlength="5000" required></textarea>
                                </div>
                                <ul v-if="validationErrors.length" class="text-danger small" role="alert">
                                    <li v-for="(message, index) in validationErrors" :key="index">{{ message }}</li>
                                </ul>
                                <button type="submit" class="btn btn-primary w-100">
                                    {{ saving ? 'Salvando e sincronizando…' : 'Agendar' }}
                                </button>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-8">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="h5 mb-0">Agendamentos por dia</h2>
                    <button class="btn btn-outline-secondary btn-sm" type="button"
                        :disabled="loading || saving" @click="loadAppointments">Atualizar lista</button>
                </div>
                <p v-if="loading" role="status">Carregando agendamentos…</p>
                <p v-else-if="!groups.length && !loadError" class="text-muted">Nenhum agendamento cadastrado.</p>
                <article v-for="group in groups" :key="group.date" class="card mb-3 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center"
                        :class="group.confirmed ? 'bg-primary text-white' : 'bg-light'">
                        <h3 class="h6 mb-0">{{ formatDate(group.date) }}</h3>
                        <span class="badge bg-dark">{{ group.items.length }} agendamento(s)</span>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li v-for="item in group.items" :key="item.id" class="list-group-item">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <strong>{{ item.hora.slice(0, 5) }}</strong>
                                <span class="badge" :class="item.google_event_id ? 'bg-success' : 'bg-warning text-dark'">
                                    {{ item.google_event_id ? 'Confirmado no Google' : 'Salvo localmente · Sincronização pendente' }}
                                </span>
                            </div>
                            <p class="mb-0 mt-2 agenda-comment">{{ item.comentario }}</p>
                        </li>
                    </ul>
                </article>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: 'Agenda',
    data() {
        return {
            form: { data: '', hora: '', comentario: '' },
            appointments: [],
            loading: false,
            saving: false,
            timezone: '',
            duration: 30,
            notice: '',
            noticeType: 'alert-success',
            loadError: '',
            validationErrors: []
        };
    },
    computed: {
        groups() {
            const days = {};
            [...this.appointments].sort((a, b) =>
                (a.data + a.hora).localeCompare(b.data + b.hora) || a.id - b.id
            ).forEach(item => {
                if (!days[item.data]) {
                    days[item.data] = { date: item.data, items: [], confirmed: false };
                }
                days[item.data].items.push(item);
                days[item.data].confirmed ||= Boolean(item.google_event_id);
            });
            return Object.values(days);
        }
    },
    mounted() {
        this.loadAppointments();
    },
    methods: {
        formatDate(date) {
            const [year, month, day] = date.split('-');
            return day + '/' + month + '/' + year;
        },
        async loadAppointments() {
            this.loading = true;
            this.loadError = '';
            try {
                const { data } = await axios.get('/agenda');
                this.appointments = data.data;
                this.timezone = data.timezone;
                this.duration = data.duration_minutes;
            } catch (error) {
                this.loadError = 'Não foi possível carregar a agenda. Verifique sua conexão e sessão e tente atualizar a lista.';
            } finally {
                this.loading = false;
            }
        },
        async schedule() {
            if (this.saving) return;
            this.validationErrors = [];
            this.notice = '';
            if (!this.form.comentario.trim()) {
                this.validationErrors = ['Preencha o comentário.'];
                return;
            }
            this.saving = true;
            try {
                const { data } = await axios.post('/agenda', { ...this.form, comentario: this.form.comentario.trim() });
                this.appointments.push(data.data);
                this.notice = data.integration.message;
                this.noticeType = data.integration.status === 'synced' ? 'alert-success' : 'alert-warning';
                this.form = { data: '', hora: '', comentario: '' };
            } catch (error) {
                const response = error.response;
                if (response && response.status === 422) {
                    this.validationErrors = Object.values(response.data.errors || {}).flat();
                } else {
                    this.noticeType = 'alert-danger';
                    this.notice = 'Não foi possível confirmar o resultado. Atualize a lista antes de tentar cadastrar novamente.';
                }
            } finally {
                this.saving = false;
            }
        }
    }
};
</script>

<style scoped>
.agenda-comment {
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}
</style>
