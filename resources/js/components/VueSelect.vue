<script setup lang="ts">
import { computed, ref } from 'vue';

const props = defineProps<{
    modelValue?: Array<string | number>;
    options?: Array<{ value: string | number; label: string }>;
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: Array<string | number>];
}>();

const open = ref(false);
const search = ref('');

const selected = computed({
    get: () => props.modelValue ?? [],
    set: (value) => emit('update:modelValue', value),
});

const filteredOptions = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return props.options ?? [];
    }

    return (props.options ?? []).filter((option) => option.label.toLowerCase().includes(query));
});

const selectedLabels = computed(() =>
    (props.options ?? [])
        .filter((option) => selected.value.includes(option.value))
        .map((option) => option.label)
);

function toggleOption(value: string | number) {
    const next = [...selected.value];
    const index = next.indexOf(value);

    if (index >= 0) {
        next.splice(index, 1);
    } else {
        next.push(value);
    }

    emit('update:modelValue', next);
}
</script>

<template>
    <div class="relative w-full">
        <button
            type="button"
            class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
            @click="open = !open"
        >
            <span class="line-clamp-1">
                <template v-if="selectedLabels.length">
                    {{ selectedLabels.join(', ') }}
                </template>
                <template v-else>
                    {{ placeholder || 'Select...' }}
                </template>
            </span>
            <span class="text-xs text-gray-500 dark:text-gray-400">▾</span>
        </button>

        <div
            v-if="open"
            class="absolute z-20 mt-2 w-full overflow-hidden rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900"
        >
            <div class="border-b border-gray-200 p-2 dark:border-gray-700">
                <input
                    v-model="search"
                    type="text"
                    class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                    placeholder="Search..."
                />
            </div>

            <div class="max-h-52 overflow-y-auto">
                <button
                    v-for="option in filteredOptions"
                    :key="String(option.value)"
                    type="button"
                    class="flex w-full items-center justify-between px-3 py-2 text-left text-sm transition hover:bg-gray-100 dark:hover:bg-gray-800"
                    @click="toggleOption(option.value)"
                >
                    <span>{{ option.label }}</span>
                    <span
                        class="h-4 w-4 rounded border border-gray-300 text-xs dark:border-gray-600"
                        :class="selected.includes(option.value) ? 'bg-blue-500 text-white' : 'bg-white text-transparent dark:bg-gray-800'"
                    >
                        ✓
                    </span>
                </button>

                <div
                    v-if="!filteredOptions.length"
                    class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400"
                >
                    No results found
                </div>
            </div>
        </div>
    </div>
</template>
