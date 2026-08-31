<template>
    <div class="alert-stack position-fixed top-0 end-0 p-3">
        <TransitionGroup class="vstack gap-2" name="alert-list" tag="div">
            <div v-for="alert in alerts" :key="alert.id"
                class="alert alert-dismissible fade show shadow-sm mb-0" :class="alertClass(alert.type)" role="alert">
                <div class="alert-message">{{ alert.message }}</div>
                <button class="btn-close" type="button" aria-label="Fechar" @click="removeAlert(alert.id)"></button>
            </div>
        </TransitionGroup>
    </div>
</template>

<script>
const ALERT_EVENT_NAME = 'app-alert';

export default {
    name: 'Alert',
    data() {
        return {
            alerts: [],
            nextAlertId: 1
        };
    },
    mounted() {
        window.addEventListener(ALERT_EVENT_NAME, this.handleAlert);
    },
    beforeUnmount() {
        window.removeEventListener(ALERT_EVENT_NAME, this.handleAlert);
        this.alerts.forEach(alert => window.clearTimeout(alert.timeout));
    },
    methods: {
        handleAlert(event) {
            const detail = event.detail || {};
            const messages = this.normalizeMessages(detail.messages);

            messages.forEach(message => {
                this.addAlert(detail.type || 'success', message, detail.duration);
            });
        },
        addAlert(type, message, duration) {
            const id = this.nextAlertId;
            const timeout = window.setTimeout(() => {
                this.removeAlert(id);
            }, this.resolveDuration(duration));

            this.nextAlertId += 1;
            this.alerts.push({
                id,
                type,
                message,
                timeout
            });
        },
        removeAlert(alertId) {
            const index = this.alerts.findIndex(alert => alert.id === alertId);

            if (index === -1) {
                return;
            }

            window.clearTimeout(this.alerts[index].timeout);
            this.alerts.splice(index, 1);
        },
        normalizeMessages(messages) {
            if (Array.isArray(messages)) {
                return messages
                    .map(message => String(message || '').trim())
                    .filter(Boolean);
            }

            const message = String(messages || '').trim();

            return message ? [message] : [];
        },
        resolveDuration(duration) {
            const value = Number(duration);

            if (Number.isFinite(value) && value >= 3000 && value <= 5000) {
                return value;
            }

            return Math.floor(3000 + Math.random() * 2001);
        },
        alertClass(type) {
            return type === 'danger' ? 'alert-danger' : 'alert-success';
        }
    }
};
</script>

<style scoped>
.alert-stack {
    max-width: min(24rem, calc(100vw - 1rem));
    pointer-events: none;
    z-index: 1080;
}

.alert {
    border-radius: 0.5rem;
    pointer-events: auto;
}

.alert-message {
    overflow-wrap: anywhere;
    padding-right: 0.5rem;
}

.alert-list-enter-active,
.alert-list-leave-active {
    transition: opacity 180ms ease, transform 180ms ease;
}

.alert-list-enter-from,
.alert-list-leave-to {
    opacity: 0;
    transform: translateX(0.5rem);
}
</style>
