<script setup>
import { ref } from 'vue';

const isOpen = ref(false);
const currentLocale = ref('EN');

const locales = [
    { code: 'en', label: 'EN' },
    { code: 'lt', label: 'LT' },
];

function switchLocale(locale) {
    currentLocale.value = locale.label;
    isOpen.value = false;

    // 🔹 hook your real logic here
    // e.g. i18n.global.locale.value = locale.code
    // or axios.post('/locale', { locale: locale.code })
}
</script>

<template>
    <div class="relative">
        <!-- Trigger -->
        <button
            @click="isOpen = !isOpen"
            class="flex items-center gap-2 rounded-lg border px-3 py-1.5 text-sm
                   bg-white hover:bg-gray-100
                   dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700"
        >
            <span class="font-medium">{{ currentLocale }}</span>
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

        <div
            v-if="isOpen"
            class="absolute right-0 mt-2 w-24 rounded-lg border bg-white shadow-lg
                   dark:bg-gray-800 dark:border-gray-700"
        >
            <button
                v-for="locale in locales"
                :key="locale.code"
                @click="switchLocale(locale)"
                class="block w-full px-3 py-2 text-sm text-left
                       hover:bg-gray-100 dark:hover:bg-gray-700"
            >
                {{ locale.label }}
            </button>
        </div>
    </div>
</template>
