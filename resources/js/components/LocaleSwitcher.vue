<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    locales: {
        type: Object,
        required: true,
    },
    currentLocale: {
        type: String,
        required: true,
    },
});

const isOpen = ref(false);

// Use a local ref seeded from the prop and synced on mount
const selectedLocale = ref(props.currentLocale ?? 'en');

function switchLocale(localeCode) {
    isOpen.value = false;

    axios.post('/locale', { locale: localeCode })
        .then(() => {
            window.location.reload();
        });
}

onMounted(() => {
    axios.get('/get-locale').then((response) => {
        selectedLocale.value = response.data.locale;
    });
});

</script>

<template>
    <div class="relative">
        <!-- Trigger -->
        <button
            @click="isOpen = !isOpen"
            class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-1.5 text-sm
                   bg-white text-gray-700 hover:bg-gray-100
                   dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700
                   focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 dark:focus:ring-offset-gray-900"
        >
            <span class="font-medium">
                {{ locales[selectedLocale] }}
            </span>
            <svg
                class="h-4 w-4 transition-transform"
                :class="{ 'rotate-180': isOpen }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown -->
        <div
            v-if="isOpen"
            class="absolute right-0 mt-2 w-32 rounded-lg border border-gray-200 bg-white shadow-lg
                   dark:bg-gray-800 dark:border-gray-700"
        >
            <button
                v-for="(label, code) in locales"
                :key="code"
                @click="switchLocale(code)"
                :class="['block w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700',
                         (code === selectedLocale ? 'font-semibold text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-200')]
                "
            >
                {{ label }}
            </button>
        </div>
    </div>
</template>
