<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="w-full max-w-sm bg-white rounded-xl shadow p-8">
            <h1 class="text-2xl font-semibold text-gray-800 mb-6">Logowanie</h1>

            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Login</label>
                    <input v-model="login" type="text" placeholder="ImięNazwisko" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data urodzenia</label>
                    <input v-model="password" type="date" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <p v-if="error" class="text-red-500 text-sm mb-4">{{ error }}</p>

                <button type="submit" :disabled="loading"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-lg text-sm disabled:opacity-50">
                    {{ loading ? 'Logowanie...' : 'Zaloguj się' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const emit    = defineEmits(['login']);
const login   = ref('');
const password = ref('');
const error   = ref('');
const loading = ref(false);

async function submit() {
    error.value   = '';
    loading.value = true;
    try {
        const res = await fetch('/api/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ login: login.value, password: password.value }),
        });
        if (!res.ok) throw new Error();
        const { token } = await res.json();
        emit('login', token);
    } catch {
        error.value = 'Nieprawidłowy login lub data urodzenia.';
    } finally {
        loading.value = false;
    }
}
</script>
