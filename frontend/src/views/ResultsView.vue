<template>
    <div class="min-h-screen bg-gray-50 p-6">
        <div class="max-w-3xl mx-auto">

            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Wyniki badań</h1>
                <button @click="$emit('logout')" class="text-sm text-gray-500 hover:text-gray-700">Wyloguj</button>
            </div>

            <div v-if="loading" class="text-gray-500 text-sm">Ładowanie...</div>

            <template v-else-if="data">
                <div class="bg-white rounded-xl shadow p-6 mb-6">
                    <h2 class="text-lg font-medium text-gray-700 mb-3">Dane pacjenta</h2>
                    <dl class="grid grid-cols-2 gap-2 text-sm">
                        <dt class="text-gray-500">Imię i nazwisko</dt>
                        <dd class="text-gray-800">{{ data.patient.name }} {{ data.patient.surname }}</dd>
                        <dt class="text-gray-500">Płeć</dt>
                        <dd class="text-gray-800">{{ { male: 'mężczyzna', female: 'kobieta' }[data.patient.sex] ?? data.patient.sex }}</dd>
                        <dt class="text-gray-500">Data urodzenia</dt>
                        <dd class="text-gray-800">{{ data.patient.birthDate }}</dd>
                    </dl>
                </div>

                <div v-for="order in data.orders" :key="order.orderId" class="bg-white rounded-xl shadow p-6 mb-4">
                    <h2 class="text-base font-medium text-gray-700 mb-3">Zlecenie #{{ order.orderId }}</h2>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="pb-3 pr-6 font-medium">Badanie</th>
                                <th class="pb-3 pr-6 font-medium">Wynik</th>
                                <th class="pb-3 font-medium">Wartość referencyjna</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="result in order.results" :key="result.name" class="border-b last:border-0">
                                <td class="py-3 pr-6 text-gray-800">{{ result.name }}</td>
                                <td class="py-3 pr-6 text-gray-800">{{ result.value }}</td>
                                <td class="py-3 text-gray-500 whitespace-pre-line">{{ formatRef(result.reference) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

defineEmits(['logout']);

const data    = ref(null);
const loading = ref(true);

onMounted(async () => {
    try {
        const res = await fetch('/api/results', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
            },
        });
        data.value = await res.json();
    } finally {
        loading.value = false;
    }
});

function formatRef(value) {
    return value.replaceAll('\\X0A\\', '\n');
}
</script>
