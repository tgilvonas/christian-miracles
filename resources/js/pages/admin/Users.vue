<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { trans } from '@/helpers/translator';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';
import { onMounted, onBeforeUnmount, ref, reactive } from 'vue';
import axios from 'axios';
import Button from '@/components/Button.vue';
import FlashMessage from '@/components/FlashMessage.vue';
import Modal from '@/components/Modal.vue';
import DeleteDialog from '@/components/DeleteDialog.vue';
import state from '@/state.js';
import eventBus from '@/eventBus.js';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('users'),
        href: route('admin.users.index'),
    },
];

const loading = ref(false);
const users = ref<any[]>([]);
const pagination = ref<any>(null);
const editingUserId = ref<number | null>(null);
const roleOptions = [
    { label: 'ROLE_SUPERADMIN', value: 'ROLE_SUPERADMIN' },
    { label: 'ROLE_EDITOR', value: 'ROLE_EDITOR' },
];

const form = reactive({
    name: '',
    email: '',
    password: '',
    roles: [] as string[],
});

const resetForm = () => {
    editingUserId.value = null;
    Object.assign(form, {
        name: '',
        email: '',
        password: '',
        roles: [] as string[],
    });
};

const refreshUsers = () => {
    getUsers();
};

function getDeleteUrl() {
    const deleteTarget = (state.modals.objectToDelete as Record<string, any>)?.objectInModal as Record<string, any> | null;
    const userId = deleteTarget?.id;

    return userId
        ? route('admin.users.delete', { userId }).toString()
        : '';
}

onMounted(() => {
    getUsers();
    eventBus.on('userSaved', refreshUsers);
    eventBus.on('objectDeleted', refreshUsers);
});

onBeforeUnmount(() => {
    eventBus.off('userSaved', refreshUsers);
    eventBus.off('objectDeleted', refreshUsers);
});

function getUsers(page: number = 1) {
    loading.value = true;

    axios.get(route('admin.users.json_list'), {
        params: {
            paginate_by: 10,
            page,
        },
    }).then(function(response) {
        users.value = response.data?.data || [];
        pagination.value = response.data || null;
    }).catch(function(error) {
        console.error(error);
    }).finally(function() {
        loading.value = false;
    });
}

function openCreateUserModal() {
    resetForm();
    state.callModal({ modal: 'user', objectId: null });
}

function openEditUserModal(user: Record<string, any>) {
    editingUserId.value = user.id;

    axios.get(route('admin.users.edit', { userId: user.id })).then(({ data }) => {
        Object.assign(form, {
            name: data.name ?? '',
            email: data.email ?? '',
            password: '',
            roles: Array.isArray(data.roles) ? data.roles : [],
        });
        state.callModal({ modal: 'user', objectId: user.id });
    }).catch(function(error) {
        console.error(error);
        state.flashErrorMessage({ message: error.response?.data?.message || 'Unable to load user' });
    });
}

function submitForm() {
    const url = editingUserId.value
        ? route('admin.users.save', { userId: editingUserId.value }).toString()
        : route('admin.users.save').toString();

    axios.post(url, {
        name: form.name,
        email: form.email,
        password: form.password,
        roles: form.roles,
    }).then((response) => {
        state.hideModal({ modal: 'user' });
        resetForm();
        state.flashSuccessMessage({ message: response.data.message });
        eventBus.emit('userSaved');
    }).catch(function(error) {
        console.error(error);
        state.flashErrorMessage({ message: error.response?.data?.message || 'Unable to save user' });
    });
}
</script>

<template>
    <Head :title="trans('users')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-3">
            <FlashMessage type="success"></FlashMessage>
            <FlashMessage type="error"></FlashMessage>

            <Modal modal-name="user">
                <template #modal_title>
                    {{ editingUserId ? trans('edit') : trans('create_new') }}
                </template>
                <template #content>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ trans('name') }}</label>
                            <input v-model="form.name" type="text" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ trans('email') }}</label>
                            <input v-model="form.email" type="email" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ trans('password') }}</label>
                            <input v-model="form.password" type="password" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ trans('roles') }}</label>
                            <div class="mt-2 space-y-2">
                                <label v-for="roleOption in roleOptions" :key="roleOption.value" class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-200">
                                    <input v-model="form.roles" type="checkbox" :value="roleOption.value" />
                                    <span>{{ roleOption.label }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <Button type="button" color="gray" @click="state.hideModal({ modal: 'user' })">
                                {{ trans('cancel') }}
                            </Button>
                            <Button type="submit" color="green">
                                {{ trans('save') }}
                            </Button>
                        </div>
                    </form>
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

            <Button @click="openCreateUserModal" color="green">
                {{ trans('create_new') }}
            </Button>

            <div class="mt-6">
                <div v-if="loading" class="text-sm text-gray-500">
                    {{ trans('loading') }}
                </div>
                <div v-else-if="users.length" class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 dark:border-gray-600 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">ID</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('name') }}</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('email') }}</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('roles') }}</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">{{ trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id" class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ user.id }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ user.name }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ user.email }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">{{ (user.roles || []).join(', ') }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">
                                    <Button @click="openEditUserModal(user)" color="blue" size="sm">
                                        {{ trans('edit') }}
                                    </Button>
                                    <Button @click="state.callModal({ modal: 'objectToDelete', objectId: user.id, objectInModal: user })" color="red" size="sm">
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
