<script setup>
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const localeEntries = computed(() => Object.entries(page.props.locales ?? {}))

function getDefaultActiveTab() {
    const currentLocale = page.props.currentLocale ?? ''
    const availableLocales = localeEntries.value.map(([localeCode]) => localeCode)

    if (availableLocales.includes(currentLocale)) {
        return currentLocale
    }

    return availableLocales[0] ?? null
}

const activeTab = ref(getDefaultActiveTab())

const emit = defineEmits(['tabChanged'])

function changeTab(locale) {
    activeTab.value = locale
    emit('tabChanged', locale)
}
</script>

<template>
    <div>
        <div class="border-b border-gray-200 dark:border-gray-700 mb-4">
            <nav class="flex gap-4">
                <span
                    v-for="[localeCode, localeLabel] in localeEntries"
                    :key="localeCode"
                    @click="changeTab(localeCode)"
                    class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
                    :class="activeTab === localeCode
                        ? 'border-blue-600 text-blue-600 cursor-pointer'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 cursor-pointer dark:text-gray-400 dark:hover:text-gray-300'"
                >
                    {{ localeLabel }}
                </span>
            </nav>
        </div>

        <div>
            <slot :activeTab="activeTab"></slot>
        </div>
    </div>
</template>
