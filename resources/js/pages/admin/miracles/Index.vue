<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { trans } from '@/helpers/translator';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';
import { onMounted, onBeforeUnmount, ref } from 'vue';
import axios from 'axios';
import Button from '@/components/Button.vue';
import FlashMessage from '@/components/FlashMessage.vue';
import Modal from '@/components/Modal.vue';
import DeleteDialog from '@/components/DeleteDialog.vue';
import state from '@/state.js';
import eventBus from '@/eventBus.js';

const createNewRoute = route('admin.miracles.edit', {
    miracleId: 'new',
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('miracles'),
        href: route('admin.miracles.index'),
    },
];

const page = usePage();
const loading = ref(false);
const miracles = ref<any[]>([]);
const pagination = ref<any>(null);
const searchText = ref('');

function getDeleteUrl() {
    const deleteTarget = (state.modals.objectToDelete as Record<string, any>)?.objectInModal as Record<string, any> | null;
    const miracleId = deleteTarget?.id;

    return miracleId
        ? route('admin.miracles.delete', { miracleId }).toString()
        : '';
}

function refreshMiracles() {
    getMiracles();
}

function getMiracles(page: number = 1) {
    loading.value = true;

    axios.get(route('admin.miracles.json_list'), {
        params: {
            paginate_by: 10,
            page,
            search_text: searchText.value,
        },
    }).then(function (response) {
        miracles.value = response.data?.data || [];
        pagination.value = response.data || null;
    }).catch(function (error) {
        console.error(error);
    }).finally(function () {
        loading.value = false;
    });
}

function searchMiracles() {
    getMiracles(1);
}

onMounted(() => {
    getMiracles();
    eventBus.on('objectDeleted', refreshMiracles);
});

onBeforeUnmount(() => {
    eventBus.off('objectDeleted', refreshMiracles);
});
</script>

<template>
    <Head :title="trans('miracles')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-3">
            <FlashMessage type="success" />
            <FlashMessage type="error" />

            <Modal modal-name="objectToDelete">
                <template #modal_title>
                    {{ trans('delete_record') }}
                </template>
                <template #content>
                    <div>
                        <DeleteDialog :delete-url="getDeleteUrl()" />
                    </div>
                </template>
            </Modal>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <Button :href="createNewRoute" color="green">
                    {{ trans('create_new') }}
                </Button>

                <div class="flex items-center gap-2">
                    <input
                        v-model="searchText"
                        type="text"
                        :placeholder="trans('search')"
                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                    />
                    <Button type="button" color="blue" @click="searchMiracles">
                        {{ trans('search') }}
                    </Button>
                </div>
            </div>

            <div class="mt-6">
                <div v-if="loading" class="text-sm text-gray-500">
                    {{ trans('loading') }}
                </div>

                <div v-else-if="miracles.length" class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 dark:border-gray-600 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">ID</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('name') }}</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('happened_at') }}</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('published') }}</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('created_at') }}</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('updated_at') }}</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="miracle in miracles" :key="miracle.id" class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ miracle.id }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ miracle['name_' + (String(page.props.currentLocale ?? 'en').split('_')[0])] || miracle['name_' + String(page.props.currentLocale ?? 'en')] || miracle.name || '-' }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ miracle.happened_at || '-' }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ miracle.published ? trans('yes') : trans('no') }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ miracle.created_at || '-' }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ miracle.updated_at || '-' }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">
                                    <Button :href="route('admin.miracles.edit', { miracleId: miracle.id })" color="blue" size="sm">
                                        {{ trans('edit') }}
                                    </Button>
                                    <Button @click="state.callModal({ modal: 'objectToDelete', objectId: miracle.id, objectInModal: miracle })" color="red" size="sm">
                                        {{ trans('delete') }}
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="mt-4 text-sm text-gray-500">
                    {{ trans('no_records_found') }}
                </div>
            </div>
        </div>
    </AppLayout>
</template>
