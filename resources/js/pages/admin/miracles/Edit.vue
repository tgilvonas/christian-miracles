<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/Button.vue';
import Tabs from '@/components/Tabs.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { trans } from '@/helpers/translator';
import { type BreadcrumbItem } from '@/types';
import { computed, reactive, watch } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    miracle?: Record<string, any>;
}>();

const page = usePage();
const localeEntries = computed(() => Object.entries(page.props.locales ?? {}));

const form = reactive({
    happened_at: '',
    published: false,
    at_holy_mass: false,
    translations: {} as Record<string, Record<string, string>>,
});

function normalizeDate(value?: string | null) {
    if (!value) {
        return '';
    }

    return String(value).slice(0, 10);
}

function syncForm() {
    form.happened_at = normalizeDate(props.miracle?.happened_at);
    form.published = Boolean(props.miracle?.published);
    form.at_holy_mass = Boolean(props.miracle?.at_holy_mass);

    const translationsByLocale = Object.fromEntries(
        (Array.isArray(props.miracle?.translations) ? props.miracle.translations : []).map((translation: Record<string, any>) => [
            translation?.lang ?? '',
            { ...translation },
        ])
    );

    const nextTranslations: Record<string, Record<string, string>> = {};

    localeEntries.value.forEach(([localeCode]) => {
        const existingTranslation = translationsByLocale[localeCode] ?? {};

        nextTranslations[localeCode] = {
            lang: localeCode,
            name: existingTranslation.name ?? '',
            slug: existingTranslation.slug ?? '',
            meta_description: existingTranslation.meta_description ?? '',
            meta_keywords: existingTranslation.meta_keywords ?? '',
            description: existingTranslation.description ?? '',
        };
    });

    form.translations = nextTranslations;
}

watch(
    () => props.miracle,
    syncForm,
    { deep: true, immediate: true }
);

watch(localeEntries, syncForm, { deep: true, immediate: true });

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('miracles'),
        href: route('admin.miracles.index'),
    },
];
</script>

<template>
    <Head :title="trans('miracles')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-3">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="space-y-6">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ trans('happened_at') }}
                            </label>
                            <input
                                v-model="form.happened_at"
                                type="date"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            />
                        </div>

                        <div class="flex flex-col gap-3">
                            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                                <input v-model="form.published" type="checkbox" class="h-4 w-4 rounded border-gray-300" />
                                <span>{{ trans('published') }}</span>
                            </label>

                            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                                <input v-model="form.at_holy_mass" type="checkbox" class="h-4 w-4 rounded border-gray-300" />
                                <span>{{ trans('at_holy_mass') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-md border border-gray-200 p-3 dark:border-gray-700">
                        <Tabs>
                            <template #default="{ activeTab }">
                                <div v-if="activeTab && form.translations[activeTab as string]" class="space-y-4">
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                            {{ trans('name') }}
                                        </label>
                                        <input
                                            v-model="form.translations[activeTab as string].name"
                                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                        />
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                            {{ trans('slug') }}
                                        </label>
                                        <input
                                            v-model="form.translations[activeTab as string].slug"
                                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                        />
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                            Meta Description
                                        </label>
                                        <textarea
                                            v-model="form.translations[activeTab as string].meta_description"
                                            rows="3"
                                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                        />
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                            Meta Keywords
                                        </label>
                                        <textarea
                                            v-model="form.translations[activeTab as string].meta_keywords"
                                            rows="3"
                                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                        />
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                            {{ trans('description') }}
                                        </label>
                                        <textarea
                                            v-model="form.translations[activeTab as string].description"
                                            rows="6"
                                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                        />
                                    </div>
                                </div>
                            </template>
                        </Tabs>
                    </div>

                    <div class="flex justify-end">
                        <Button :href="route('admin.miracles.index')" type="button" color="gray">
                            {{ trans('cancel') }}
                        </Button>
                        <Button type="button" color="blue">
                            {{ trans('save') }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
