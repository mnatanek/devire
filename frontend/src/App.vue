<template>
    <LoginView v-if="!token" @login="onLogin" />
    <ResultsView v-else @logout="onLogout" />
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import LoginView from './views/LoginView.vue';
import ResultsView from './views/ResultsView.vue';

const token = ref(localStorage.getItem('token'));
let logoutTimer = null;

function getTokenExpiry(t) {
    try {
        const payload = JSON.parse(atob(t.split('.')[1]));
        return payload.exp * 1000;
    } catch {
        return null;
    }
}

function scheduleAutoLogout(t) {
    const expiry = getTokenExpiry(t);
    if (!expiry) return;

    const delay = expiry - Date.now();
    if (delay <= 0) {
        onLogout();
        return;
    }

    logoutTimer = setTimeout(onLogout, delay);
}

function onLogin(t) {
    localStorage.setItem('token', t);
    token.value = t;
    scheduleAutoLogout(t);
}

function onLogout() {
    clearTimeout(logoutTimer);
    localStorage.removeItem('token');
    token.value = null;
}

onMounted(() => {
    if (token.value) scheduleAutoLogout(token.value);
});

onUnmounted(() => clearTimeout(logoutTimer));
</script>
