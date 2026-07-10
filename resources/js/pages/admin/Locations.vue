<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { trans } from '@/helpers/translator';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js'
import { ref, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';
import Button from '@/components/Button.vue';
import FlashMessage from '@/components/FlashMessage.vue';
import Modal from '@/components/Modal.vue';
import state from '@/state.js';
import LocationForm from '@/components/LocationForm.vue';
import eventBus from '@/eventBus.js';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('locations'),
        href: route('admin.locations.index'),
    },
];

const loading = ref(false);
const locations = ref<any[]>([]);
const pagination = ref<any>(null);
const searchText = ref('');

const refreshLocations = () => {
    getLocations();
};

onMounted(() => {
    getLocations();
    eventBus.on('locationSaved', refreshLocations);
});
onBeforeUnmount(() => {
    eventBus.off('locationSaved', refreshLocations);
});

function getLocations(page: number = 1) {
    loading.value = true;

    axios.get(route('admin.locations.json_list'), {
        params: {
            paginate_by: 10,
            page,
        }
    }).then(function(response) {
        locations.value = response.data?.data || [];
        pagination.value = response.data || null;
    }).catch(function(error) {
        console.error(error);
    }).finally(function() {
        loading.value = false;
    });
}

function getLocationValue(record: Record<string, any>, prefix: string) {
    const field = Object.keys(record || {}).find((key) => key.startsWith(prefix));
    return field ? record[field] : '-';
}

function openCreateLocationModal() {
    state.callModal({ modal: 'location', objectId: null });
}
</script>

<template>
    <Head :title="trans('locations')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-3">
            <FlashMessage type="success"></FlashMessage>
            <FlashMessage type="error"></FlashMessage>
            <Modal modal-name="location">
                <template #modal_title>
                    {{ trans('location') }}
                </template>
                <template #content>
                    <div>
                        <LocationForm />
                    </div>
                </template>
            </Modal>
            <Button @click="openCreateLocationModal" color="green">
                {{ trans('create_new') }}
            </Button>

            <div class="mt-6">
                <div v-if="loading" class="text-sm text-gray-500">
                    {{ trans('loading') }}
                </div>
                <div v-else-if="locations.length" class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 dark:border-gray-600 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">ID</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('title') }}</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('slug') }}</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="location in locations" :key="location.id" class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ location.id }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ getLocationValue(location, 'name_') }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ getLocationValue(location, 'slug_') }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">
                                    
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
