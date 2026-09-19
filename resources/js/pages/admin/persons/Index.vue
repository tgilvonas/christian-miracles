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

const createNewRoute = route('admin.persons.edit', {
    personId: 'new',
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('persons'),
        href: route('admin.persons.index'),
    },
];

const page = usePage();
const loading = ref(false);
const persons = ref<any[]>([]);
const pagination = ref<any>(null);
const searchText = ref('');
const locations = ref<any[]>([]);
const socialStatuses = ref<any[]>([]);
const selectedLocationId = ref<number | string | null>(null);
const selectedSocialStatusId = ref<number | string | null>(null);

function getLocationLabel(location: Record<string, any>) {
    const localeCode = String(page.props.currentLocale ?? 'en').toLowerCase();
    const shortLocale = localeCode.split('_')[0];

    if (location?.[`name_${localeCode}`]) {
        return location[`name_${localeCode}`];
    }

    if (location?.[`name_${shortLocale}`]) {
        return location[`name_${shortLocale}`];
    }

    if (location?.name) {
        return location.name;
    }

    return `Location #${location?.id ?? '-'}`;
}

function getSocialStatusLabel(socialStatus: Record<string, any>) {
    const localeCode = String(page.props.currentLocale ?? 'en').toLowerCase();
    const shortLocale = localeCode.split('_')[0];

    if (socialStatus?.[`name_${localeCode}`]) {
        return socialStatus[`name_${localeCode}`];
    }

    if (socialStatus?.[`name_${shortLocale}`]) {
        return socialStatus[`name_${shortLocale}`];
    }

    if (socialStatus?.name) {
        return socialStatus.name;
    }

    return `Social status #${socialStatus?.id ?? '-'}`;
}

function loadFilterOptions() {
    Promise.all([
        axios.get(route('admin.locations.json_list'), {
            params: { paginate_by: 9999 },
        }),
        axios.get(route('admin.social_statuses.json_list'), {
            params: { paginate_by: 9999 },
        }),
    ]).then(([locationsResponse, socialStatusesResponse]) => {
        locations.value = (locationsResponse.data?.data ?? locationsResponse.data ?? []).map((location: Record<string, any>) => ({
            id: Number(location.id),
            name: getLocationLabel(location),
        }));

        socialStatuses.value = (socialStatusesResponse.data?.data ?? socialStatusesResponse.data ?? []).map((socialStatus: Record<string, any>) => ({
            id: Number(socialStatus.id),
            name: getSocialStatusLabel(socialStatus),
        }));
    }).catch((error) => {
        console.error('Unable to load person filters', error);
    });
}

function getDeleteUrl() {
    const deleteTarget = (state.modals.objectToDelete as Record<string, any>)?.objectInModal as Record<string, any> | null;
    const personId = deleteTarget?.id;

    return personId
        ? route('admin.persons.delete', { personId }).toString()
        : '';
}

function refreshPersons() {
    getPersons();
}

function getPersons(page: number = 1) {
    loading.value = true;

    axios.get(route('admin.persons.json_list'), {
        params: {
            paginate_by: 10,
            page,
            search_text: searchText.value,
            location_id: selectedLocationId.value || undefined,
            social_status_id: selectedSocialStatusId.value || undefined,
        },
    }).then(function (response) {
        persons.value = response.data?.data || [];
        pagination.value = response.data || null;
    }).catch(function (error) {
        console.error(error);
    }).finally(function () {
        loading.value = false;
    });
}

function searchPersons() {
    getPersons(1);
}

function clearFilters() {
    searchText.value = '';
    selectedLocationId.value = null;
    selectedSocialStatusId.value = null;
    getPersons(1);
}

onMounted(() => {
    getPersons();
    loadFilterOptions();
    eventBus.on('objectDeleted', refreshPersons);
});

onBeforeUnmount(() => {
    eventBus.off('objectDeleted', refreshPersons);
});
</script>

<template>
    <Head :title="trans('persons')" />
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

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                    <input
                        v-model="searchText"
                        type="text"
                        :placeholder="trans('search')"
                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 sm:w-52"
                    />

                    <select
                        v-model="selectedLocationId"
                        @change="searchPersons"
                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 sm:w-48"
                    >
                        <option :value="null">{{ trans('all_locations') }}</option>
                        <option v-for="location in locations" :key="location.id" :value="location.id">
                            {{ location.name }}
                        </option>
                    </select>

                    <select
                        v-model="selectedSocialStatusId"
                        @change="searchPersons"
                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 sm:w-48"
                    >
                        <option :value="null">{{ trans('all_social_statuses') }}</option>
                        <option v-for="socialStatus in socialStatuses" :key="socialStatus.id" :value="socialStatus.id">
                            {{ socialStatus.name }}
                        </option>
                    </select>

                    <Button type="button" color="blue" @click="searchPersons">
                        {{ trans('search') }}
                    </Button>

                    <Button v-if="searchText || selectedLocationId || selectedSocialStatusId" type="button" color="gray" @click="clearFilters">
                        {{ trans('clear') }}
                    </Button>
                </div>
            </div>

            <div class="mt-6">
                <div v-if="loading" class="text-sm text-gray-500">
                    {{ trans('loading') }}
                </div>

                <div v-else-if="persons.length" class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 dark:border-gray-600 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">ID</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('name') }}</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('beatified_at') }}</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('canonized_at') }}</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('published') }}</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('created_at') }}</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('updated_at') }}</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="person in persons" :key="person.id" class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ person.id }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ person.name || '-' }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ person.beatified_at || '-' }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ person.canonized_at || '-' }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ person.published ? trans('yes') : trans('no') }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ person.created_at || '-' }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ person.updated_at || '-' }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">
                                    <Button :href="route('admin.persons.edit', { personId: person.id })" color="blue" size="sm">
                                        {{ trans('edit') }}
                                    </Button>
                                    <Button @click="state.callModal({ modal: 'objectToDelete', objectId: person.id, objectInModal: person })" color="red" size="sm">
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
