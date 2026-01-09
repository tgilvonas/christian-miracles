<script setup>
import { ref } from 'vue';
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

console.log(props.currentLocale);

function switchLocale(localeCode) {
    isOpen.value = false;

    axios.post('/locale', { locale: localeCode })
        .then(() => {
            window.location.reload();
        });
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
            <span class="font-medium">
                {{ locales[currentLocale] }}
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
            class="absolute right-0 mt-2 w-24 rounded-lg border bg-white shadow-lg
                   dark:bg-gray-800 dark:border-gray-700"
        >
            <button
                v-for="(label, code) in locales"
                :key="code"
                @click="switchLocale(code)"
                class="block w-full px-3 py-2 text-left text-sm
                       hover:bg-gray-100 dark:hover:bg-gray-700"
            >
                {{ label }}
            </button>
        </div>
    </div>
</template>
