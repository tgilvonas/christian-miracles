<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/Button.vue';
import Tabs from '@/components/Tabs.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { trans } from '@/helpers/translator';
import { type BreadcrumbItem } from '@/types';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import { Ckeditor } from '@ckeditor/ckeditor5-vue';
import { computed, reactive, watch } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    miracle?: Record<string, any>;
}>();

const page = usePage();
const localeEntries = computed(() => Object.entries(page.props.locales ?? {}));
const ckeditor = Ckeditor;
const editorConfig = {
    toolbar: ['sourceEditing', 'bold', 'italic', 'underline', 'bulletedList', 'blockQuote', 'link'],
    shouldNotGroupWhenFull: true,
    height: 700,
    ui: {
        poweredBy: {
            position: 'outside',
        },
    },
    heading: {
        options: [{ model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' }],
    },
    styles: {
        definitions: [],
    },
    contentStyles: [
        'body { color: #111827; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; }',
        'p { margin: 0 0 0.75em; }',
    ]
};

const form = reactive({
    happened_at: '',
    year_to: '',
    published: false,
    at_holy_mass: false,
    translations: {} as Record<string, Record<string, string>>,
    texts: {} as Record<string, Array<Record<string, any>>>,
});

function normalizeDate(value?: string | null) {
    if (!value) {
        return '';
    }

    return String(value).slice(0, 10);
}

function syncForm() {
    form.happened_at = normalizeDate(props.miracle?.happened_at);
    form.year_to = props.miracle?.year_to;
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

    const nextTexts: Record<string, Array<Record<string, any>>> = {};

    localeEntries.value.forEach(([localeCode]) => {
        const existingTexts = (Array.isArray(props.miracle?.texts) ? props.miracle.texts : [])
            .filter((textItem: Record<string, any>) => (textItem?.lang ?? '') === localeCode)
            .map((textItem: Record<string, any>) => ({
                ...textItem,
                pos: Number(textItem?.pos ?? 0) || 0,
                text: textItem?.text ?? '',
            }))
            .sort((left, right) => (left.pos ?? 0) - (right.pos ?? 0));

        nextTexts[localeCode] = existingTexts.length > 0
            ? existingTexts
            : [{ lang: localeCode, pos: 1, text: '' }];
    });

    form.translations = nextTranslations;
    form.texts = nextTexts;
}

function addTextItem(localeCode: string) {
    const existingItems = Array.isArray(form.texts[localeCode]) ? form.texts[localeCode] : [];
    const nextItems = [
        ...existingItems,
        {
            lang: localeCode,
            pos: existingItems.length + 1,
            title: '',
            text: '',
            citation: '',
        },
    ];

    form.texts[localeCode] = nextItems;
}

function removeTextItem(localeCode: string, index: number) {
    const existingItems = Array.isArray(form.texts[localeCode]) ? [...form.texts[localeCode]] : [];

    if (index < 0 || index >= existingItems.length) {
        return;
    }

    existingItems.splice(index, 1);
    existingItems.forEach((item, itemIndex) => {
        item.pos = itemIndex + 1;
    });

    form.texts[localeCode] = existingItems;
}

function moveTextItem(localeCode: string, index: number, direction: -1 | 1) {
    const existingItems = Array.isArray(form.texts[localeCode]) ? [...form.texts[localeCode]] : [];
    const targetIndex = index + direction;

    if (targetIndex < 0 || targetIndex >= existingItems.length) {
        return;
    }

    const [movedItem] = existingItems.splice(index, 1);
    existingItems.splice(targetIndex, 0, movedItem);
    existingItems.forEach((item, itemIndex) => {
        item.pos = itemIndex + 1;
    });

    form.texts[localeCode] = existingItems;
}

function updateEditorContent(localeCode: string, index: number, event: Event) {
    const target = event.target as HTMLElement | null;
    const existingItems = Array.isArray(form.texts[localeCode]) ? [...form.texts[localeCode]] : [];

    if (!target || index < 0 || index >= existingItems.length) {
        return;
    }

    existingItems[index].text = target.innerHTML;
    form.texts[localeCode] = existingItems;
}

watch(
    () => props.miracle,
    syncForm,
    { deep: true, immediate: true }
);

watch(localeEntries, syncForm, { deep: true, immediate: true });

function submit() {
    const saveRoute = props.miracle?.id
        ? route('admin.miracles.save', { miracleId: props.miracle.id })
        : route('admin.miracles.save');

    router.post(saveRoute, form, {
        preserveScroll: true,
        preserveState: false,
    });
}

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
                <form class="space-y-6" @submit.prevent="submit">
                    <div class="grid gap-4 md:grid-cols-1">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ trans('happened_at') }}
                            </label>
                            <input
                                v-model="form.happened_at"
                                type="date"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            />
                            <label class="mt-3 mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ trans('year_to') }}
                            </label>
                            <input
                                v-model="form.year_to"
                                type="text"
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
                                <div v-if="activeTab && form.translations[activeTab as string]" class="space-y-6">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                                {{ trans('name') }}
                                            </label>
                                            <input
                                                v-model="form.translations[activeTab as string].name"
                                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" required
                                            />
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                                {{ trans('slug') }}
                                            </label>
                                            <div class="flex items-center gap-2">
                                                /
                                                <input
                                                    v-model="form.translations[activeTab as string].slug"
                                                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                                />
                                            </div>
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

                                    <div class="space-y-3 rounded-md border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/50">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                                {{ trans('texts') }}
                                            </h3>
                                            <Button type="button" color="green" @click="addTextItem(activeTab as string)">
                                                {{ trans('add') }}
                                            </Button>
                                        </div>

                                        <div
                                            v-if="activeTab && Array.isArray(form.texts[activeTab as string])"
                                            class="space-y-3"
                                        >
                                            <div
                                                v-for="(textItem, index) in form.texts[activeTab as string]"
                                                :key="`${activeTab}-${index}`"
                                                class="rounded-md border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-900"
                                            >
                                                <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                        {{ trans('block') }} {{ index + 1 }}
                                                    </span>
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <label class="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-300">
                                                            <span>{{ trans('position') }}</span>
                                                            <input
                                                                v-model.number="textItem.pos"
                                                                type="number"
                                                                min="1"
                                                                class="w-16 rounded border border-gray-300 px-2 py-1 text-xs dark:border-gray-600 dark:bg-gray-800"
                                                            />
                                                        </label>
                                                        <button
                                                            type="button"
                                                            class="rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                                                            @click="moveTextItem(activeTab as string, index, -1)"
                                                        >
                                                            ↑
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                                                            @click="moveTextItem(activeTab as string, index, 1)"
                                                        >
                                                            ↓
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="rounded border border-red-300 px-2 py-1 text-xs text-red-600 hover:bg-red-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/30"
                                                            @click="removeTextItem(activeTab as string, index)"
                                                        >
                                                            {{ trans('remove') }}
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-2">
                                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                                        {{ trans('title') }}
                                                        </label>
                                                    <input
                                                        v-model="textItem.title"
                                                        type="text"
                                                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 mb-1"
                                                    />
                                                </div>

                                                <div class="text-black">
                                                    <ckeditor
                                                    :editor="ClassicEditor"
                                                    v-model="textItem.text"
                                                    :config="editorConfig"
                                                    class="block min-h-80 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-black shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                                                />
                                                </div>

                                                <div class="mt-1">
                                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                                        {{ trans('info_source') }}
                                                        </label>
                                                    <input
                                                        v-model="textItem.info_source"
                                                        type="text"
                                                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                                    />
                                                </div>
                                            </div>

                                            <div class="mt-2 text-right text-gray-500 dark:text-gray-400">
                                                <Button type="button" color="green" @click="addTextItem(activeTab as string)">
                                                    {{ trans('add') }}
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Tabs>
                    </div>

                    <div class="flex justify-end">
                        <Button :href="route('admin.miracles.index')" type="button" color="gray">
                            {{ trans('cancel') }}
                        </Button>
                        <Button type="submit" color="blue">
                            {{ trans('save') }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
