const ALERT_EVENT_NAME = 'app-alert';

export function alertSuccess(messages, options = {}) {
    dispatchAlert('success', messages, options);
}

export function alertDanger(messages, options = {}) {
    dispatchAlert('danger', messages, options);
}

export function registerAlerts(app) {
    window.alertSuccess = alertSuccess;
    window.alertDanger = alertDanger;

    app.config.globalProperties.alertSuccess = alertSuccess;
    app.config.globalProperties.alertDanger = alertDanger;
}

function dispatchAlert(type, messages, options = {}) {
    window.dispatchEvent(new CustomEvent(ALERT_EVENT_NAME, {
        detail: {
            type,
            messages: normalizeAlertMessages(messages),
            duration: options.duration
        }
    }));
}

function normalizeAlertMessages(value) {
    if (Array.isArray(value)) {
        return value.flatMap(item => normalizeAlertMessages(item)).filter(Boolean);
    }

    if (!value) {
        return [];
    }

    if (typeof value === 'string') {
        return [value];
    }

    if (value.response && value.response.data) {
        return normalizeAlertMessages(value.response.data);
    }

    if (value.errors) {
        return normalizeAlertMessages(Object.values(value.errors));
    }

    if (value.message) {
        return [value.message];
    }

    if (typeof value === 'object') {
        return normalizeAlertMessages(Object.values(value));
    }

    return [String(value)];
}
