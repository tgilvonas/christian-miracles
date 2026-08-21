<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/Button.vue';
import FlashMessage from '@/components/FlashMessage.vue';
import { Head } from '@inertiajs/vue3';
import { trans } from '@/helpers/translator';
import { type BreadcrumbItem } from '@/types';
import axios from 'axios';
import { reactive, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import state from '@/state.js';

const props = defineProps<{
    person?: Record<string, any>;
}>();

const personState = ref<Record<string, any> | undefined>(props.person);

const form = reactive({
    name: '',
    beatified_at: '',
    canonized_at: '',
    published: false,
});

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
}

watch(
    () => props.person,
    (value) => {
        personState.value = value;
        syncForm();
    },
    { deep: true, immediate: true }
);

function submit() {
    const currentPersonId = personState.value?.id ?? props.person?.id;
    const saveRoute = currentPersonId
        ? route('admin.persons.save', { personId: currentPersonId })
        : route('admin.persons.save');

    axios.post(saveRoute, form)
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
