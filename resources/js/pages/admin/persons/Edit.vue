<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/Button.vue';
import FlashMessage from '@/components/FlashMessage.vue';
import Tabs from '@/components/Tabs.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { trans } from '@/helpers/translator';
import { type BreadcrumbItem } from '@/types';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import { Ckeditor } from '@ckeditor/ckeditor5-vue';
import axios from 'axios';
import { computed, reactive, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import state from '@/state.js';

const props = defineProps<{
    person?: Record<string, any>;
}>();

const personState = ref<Record<string, any> | undefined>(props.person);
const page = usePage();
const localeEntries = computed(() => Object.entries(page.props.locales ?? {}));
const ckeditor = Ckeditor;
const editorCtor: any = ClassicEditor;
const editorConfig: any = {
    toolbar: ['sourceEditing', 'bold', 'italic', 'underline', 'bulletedList', 'blockQuote', 'link'],
    shouldNotGroupWhenFull: true,
    height: 700,
    ui: {
        poweredBy: {
            position: 'outside' as const,
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
    name: '',
    beatified_at: '',
    canonized_at: '',
    published: false,
    remove_intro_image: false,
    intro_image: null as File | null,
    intro_image_url: '',
    intro_image_preview: '',
    translations: {} as Record<string, Record<string, string>>,
    texts: {} as Record<string, Array<Record<string, any>>>,
});

function buildFormData(payload: Record<string, any>) {
    const formData = new FormData();

    function appendValue(value: any, key?: string) {
        if (value === undefined || value === null) {
            return;
        }

        if (value instanceof File) {
            if (key) {
                formData.append(key, value);
            }
            return;
        }

        if (Array.isArray(value)) {
            value.forEach((item, index) => {
                appendValue(item, `${key}[${index}]`);
            });
            return;
        }

        if (typeof value === 'object') {
            Object.entries(value).forEach(([childKey, childValue]) => {
                if (childKey === 'image_url' || childKey === 'image_preview' || childKey === 'intro_image_url' || childKey === 'intro_image_preview') {
                    return;
                }

                appendValue(childValue, key ? `${key}[${childKey}]` : childKey);
            });
            return;
        }

        if (key) {
            formData.append(key, String(value));
        }
    }

    appendValue(payload);

    return formData;
}

function normalizeDate(value?: string | null) {
    if (!value) {
        return '';
    }

    return String(value).slice(0, 10);
}

function syncForm() {
    const person = personState.value ?? props.person;

    form.name = person?.name ?? '';
    form.beatified_at = normalizeDate(person?.beatified_at);
    form.canonized_at = normalizeDate(person?.canonized_at);
    form.published = Boolean(person?.published);
    form.remove_intro_image = false;
    form.intro_image = null;
    form.intro_image_preview = '';
    form.intro_image_url = person?.intro_image_url ?? '';

    const translationsByLocale = Object.fromEntries(
        (Array.isArray(person?.translations) ? person.translations : []).map((translation: Record<string, any>) => [
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
            biography: existingTranslation.biography ?? '',
        };
    });

    const nextTexts: Record<string, Array<Record<string, any>>> = {};

    localeEntries.value.forEach(([localeCode]) => {
        const existingTexts = (Array.isArray(person?.texts) ? person.texts : [])
            .filter((textItem: Record<string, any>) => (textItem?.lang ?? '') === localeCode)
            .map((textItem: Record<string, any>) => ({
                ...textItem,
                pos: Number(textItem?.pos ?? 0) || 0,
                title: textItem?.title ?? '',
                text: textItem?.text ?? '',
                info_source: textItem?.info_source ?? '',
                image_url: textItem?.image_url ?? '',
                image_preview: '',
                remove_image: false,
                image: null,
            }))
            .sort((left, right) => (left.pos ?? 0) - (right.pos ?? 0));

        nextTexts[localeCode] = existingTexts.length > 0
            ? existingTexts
            : [{ lang: localeCode, pos: 1, title: '', text: '', info_source: '', image_url: '', image_preview: '', remove_image: false, image: null }];
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
            info_source: '',
            image_url: '',
            image_preview: '',
            remove_image: false,
            image: null,
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

function handleImageChange(localeCode: string, index: number, event: Event) {
    const input = event.target as HTMLInputElement | null;
    const file = input?.files?.[0] ?? null;
    const existingItems = Array.isArray(form.texts[localeCode]) ? [...form.texts[localeCode]] : [];

    if (index < 0 || index >= existingItems.length) {
        return;
    }

    const previousPreview = existingItems[index]?.image_preview;

    if (previousPreview) {
        URL.revokeObjectURL(previousPreview);
    }

    existingItems[index] = {
        ...existingItems[index],
        image: file,
        image_preview: file ? URL.createObjectURL(file) : '',
        image_url: file ? '' : existingItems[index]?.image_url ?? '',
        remove_image: false,
    };

    form.texts[localeCode] = existingItems;
}

function clearTextImage(localeCode: string, index: number) {
    const existingItems = Array.isArray(form.texts[localeCode]) ? [...form.texts[localeCode]] : [];

    if (index < 0 || index >= existingItems.length) {
        return;
    }

    const previousPreview = existingItems[index]?.image_preview;

    if (previousPreview) {
        URL.revokeObjectURL(previousPreview);
    }

    existingItems[index] = {
        ...existingItems[index],
        image: null,
        image_preview: '',
        image_url: '',
        remove_image: true,
    };

    form.texts[localeCode] = existingItems;
}

function handleIntroImageChange(event: Event) {
    const input = event.target as HTMLInputElement | null;
    const file = input?.files?.[0] ?? null;
    const previousPreview = form.intro_image_preview;

    if (previousPreview) {
        URL.revokeObjectURL(previousPreview);
    }

    form.remove_intro_image = false;
    form.intro_image = file;
    form.intro_image_preview = file ? URL.createObjectURL(file) : '';
}

function clearIntroImage() {
    const previousPreview = form.intro_image_preview;

    if (previousPreview) {
        URL.revokeObjectURL(previousPreview);
    }

    form.remove_intro_image = true;
    form.intro_image = null;
    form.intro_image_preview = '';
    form.intro_image_url = '';
}

watch(
    () => props.person,
    (value) => {
        personState.value = value;
        syncForm();
    },
    { deep: true, immediate: true }
);

watch(localeEntries, syncForm, { deep: true, immediate: true });

function submit() {
    const currentPersonId = personState.value?.id ?? props.person?.id;
    const saveRoute = currentPersonId
        ? route('admin.persons.save', { personId: currentPersonId })
        : route('admin.persons.save');

    axios.post(saveRoute, buildFormData(form))
        .then((response) => {
            const nextPerson = response.data?.person ?? personState.value;

            if (nextPerson) {
                personState.value = nextPerson;
            }

            syncForm();
            state.flashSuccessMessage({
                message: response.data?.message || trans('record_saved_successfully'),
            });
        })
        .catch(() => {
            state.flashErrorMessage({ message: trans('error') });
        });
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('persons'),
        href: route('admin.persons.index'),
    },
];
</script>

<template>
    <Head :title="trans('persons')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-3">
            <FlashMessage type="success" />
            <FlashMessage type="error" />
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <form class="space-y-6" @submit.prevent="submit">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ trans('persons_name') }}
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                required
                            />
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ trans('beatified_at') }}
                            </label>
                            <input
                                v-model="form.beatified_at"
                                type="date"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            />
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ trans('canonized_at') }}
                            </label>
                            <input
                                v-model="form.canonized_at"
                                type="date"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                                <input v-model="form.published" type="checkbox" class="h-4 w-4 rounded border-gray-300" />
                                <span>{{ trans('published') }}</span>
                            </label>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ trans('intro_image') }}
                            </label>
                            <input
                                type="file"
                                accept="image/*"
                                @change="handleIntroImageChange($event)"
                                class="w-full text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100 dark:text-gray-100"
                            />
                            <div v-if="form.intro_image_url || form.intro_image_preview" class="mt-3 flex items-center gap-2">
                                <label class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                    <input v-model="form.remove_intro_image" type="checkbox" class="h-4 w-4 rounded border-gray-300" />
                                    <span>{{ trans('remove_image') }}</span>
                                </label>
                            </div>
                            <p v-if="form.intro_image?.name" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Selected file: {{ form.intro_image.name }}
                            </p>
                            <img
                                v-if="!form.remove_intro_image && form.intro_image_preview"
                                :src="form.intro_image_preview"
                                alt=""
                                class="mt-2 max-h-40 w-full object-contain object-left rounded border border-gray-200 dark:border-gray-700"
                            />
                            <img
                                v-else-if="!form.remove_intro_image && form.intro_image_url"
                                :src="form.intro_image_url"
                                alt=""
                                class="mt-2 max-h-40 w-full object-contain object-left rounded border border-gray-200 dark:border-gray-700"
                            />
                        </div>
                    </div>

                    <div class="rounded-md border border-gray-200 p-3 dark:border-gray-700">
                        <Tabs>
                            <template #default="{ activeTab }">
                                <div v-if="activeTab && form.translations[activeTab as string]" class="space-y-6">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                                {{ trans('persons_name') }}
                                            </label>
                                            <input
                                                v-model="form.translations[activeTab as string].name"
                                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                                required
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
                                                v-model="form.translations[activeTab as string].biography"
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
                                                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                                    />
                                                </div>

                                                <div class="mb-2">
                                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                                        {{ trans('image') }}
                                                    </label>
                                                    <input
                                                        type="file"
                                                        accept="image/*"
                                                        @change="handleImageChange(activeTab as string, index, $event)"
                                                        class="w-full text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100 dark:text-gray-100"
                                                    />
                                                    <div v-if="textItem.image_url || textItem.image_preview || textItem.image" class="mt-3 flex items-center gap-2">
                                                        <label class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                                            <input v-model="textItem.remove_image" type="checkbox" class="h-4 w-4 rounded border-gray-300" />
                                                            <span>{{ trans('remove_image') }}</span>
                                                        </label>
                                                    </div>
                                                    <p v-if="textItem.image?.name" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                        Selected file: {{ textItem.image.name }}
                                                    </p>
                                                    <img
                                                        v-if="!textItem.remove_image && textItem.image_preview"
                                                        :src="textItem.image_preview"
                                                        alt=""
                                                        class="mt-2 max-h-40 w-full object-contain object-left rounded border border-gray-200 dark:border-gray-700"
                                                    />
                                                    <img
                                                        v-else-if="!textItem.remove_image && textItem.image_url"
                                                        :src="textItem.image_url"
                                                        alt=""
                                                        class="mt-2 max-h-40 w-full object-contain object-left rounded border border-gray-200 dark:border-gray-700"
                                                    />
                                                </div>

                                                <div class="text-black">
                                                    <ckeditor
                                                        :editor="editorCtor"
                                                        v-model="textItem.text"
                                                        :config="editorConfig"
                                                        class="block min-h-80 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-black shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                                                    />
                                                </div>

                                                <div class="mt-3">
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

                    <div class="flex justify-end gap-2">
                        <Button :href="route('admin.persons.index')" type="button" color="gray">
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
