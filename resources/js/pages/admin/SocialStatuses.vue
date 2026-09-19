<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { trans } from '@/helpers/translator';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';
import { ref, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';
import Button from '@/components/Button.vue';
import FlashMessage from '@/components/FlashMessage.vue';
import Modal from '@/components/Modal.vue';
import DeleteDialog from '@/components/DeleteDialog.vue';
import state from '@/state.js';
import SocialStatusForm from '@/components/SocialStatusForm.vue';
import eventBus from '@/eventBus.js';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('social_statuses'),
        href: route('admin.social_statuses.index'),
    },
];

const loading = ref(false);
const socialStatuses = ref<any[]>([]);
const pagination = ref<any>(null);

const refreshSocialStatuses = () => {
    getSocialStatuses();
};

function getDeleteUrl() {
    const deleteTarget = (state.modals.objectToDelete as Record<string, any>)?.objectInModal as Record<string, any> | null;
    const socialStatusId = deleteTarget?.id;

    return socialStatusId
        ? route('admin.social_statuses.delete', { socialStatusId }).toString()
        : '';
}

onMounted(() => {
    getSocialStatuses();
    eventBus.on('socialStatusSaved', refreshSocialStatuses);
    eventBus.on('objectDeleted', refreshSocialStatuses);
});
onBeforeUnmount(() => {
    eventBus.off('socialStatusSaved', refreshSocialStatuses);
    eventBus.off('objectDeleted', refreshSocialStatuses);
});

function getSocialStatuses(page: number = 1) {
    loading.value = true;

    axios.get(route('admin.social_statuses.json_list'), {
        params: {
            paginate_by: 10,
            page,
        }
    }).then(function(response) {
        socialStatuses.value = response.data?.data || [];
        pagination.value = response.data || null;
    }).catch(function(error) {
        console.error(error);
    }).finally(function() {
        loading.value = false;
    });
}

function getSocialStatusValue(record: Record<string, any>, prefix: string) {
    const field = Object.keys(record || {}).find((key) => key.startsWith(prefix));
    return field ? record[field] : '-';
}

function openCreateSocialStatusModal() {
    state.callModal({ modal: 'socialStatus', objectId: null });
}
</script>

<template>
    <Head :title="trans('social_statuses')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-3">
            <FlashMessage type="success"></FlashMessage>
            <FlashMessage type="error"></FlashMessage>

            <Modal modal-name="socialStatus">
                <template #modal_title>
                    {{ trans('social_status') }}
                </template>
                <template #content>
                    <div>
                        <SocialStatusForm />
                    </div>
                </template>
            </Modal>
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

            <Button @click="openCreateSocialStatusModal" color="green">
                {{ trans('create_new') }}
            </Button>

            <div class="mt-6">
                <div v-if="loading" class="text-sm text-gray-500">
                    {{ trans('loading') }}
                </div>
                <div v-else-if="socialStatuses.length" class="overflow-x-auto">
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
                            <tr v-for="socialStatus in socialStatuses" :key="socialStatus.id" class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ socialStatus.id }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ getSocialStatusValue(socialStatus, 'name_') }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ getSocialStatusValue(socialStatus, 'slug_') }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">
                                    <Button @click="state.callModal({ modal: 'socialStatus', objectId: socialStatus.id })" color="blue" size="sm">
                                        {{ trans('edit') }}
                                    </Button>
                                    <Button @click="state.callModal({ modal: 'objectToDelete', objectId: socialStatus.id, objectInModal: socialStatus })" color="red" size="sm">
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
