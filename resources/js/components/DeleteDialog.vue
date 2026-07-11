<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { trans } from '@/helpers/translator';
import state from '@/state.js';
import Button from '@/components/Button.vue';
import axios from "axios";
import emitter from '@/eventBus.js';

const props = defineProps({
    deleteUrl: {
        type: String,
        required: true
    }
});

const page = usePage();
const activeLocale = computed(() => String(page.props.currentLocale ?? 'en').toLowerCase());

function getDeleteTarget() {
    return (state.modals.objectToDelete as Record<string, any>)?.objectInModal as Record<string, any> | null;
}

function getDisplayName() {
    const deleteTarget = getDeleteTarget();
    if (!deleteTarget) {
        return '';
    }

    const localeCode = String(activeLocale.value).split('_')[0];
    const localeKeys = [`name_${localeCode}`, `name_${activeLocale.value}`];

    for (const key of localeKeys) {
        const value = deleteTarget[key];
        if (typeof value === 'string' && value.trim()) {
            return value;
        }
    }

    const fallbackName = Object.keys(deleteTarget)
        .filter((key) => key.startsWith('name_'))
        .map((key) => deleteTarget[key])
        .find((value) => typeof value === 'string' && value.trim());

    if (fallbackName) {
        return fallbackName;
    }

    return deleteTarget.title ?? deleteTarget.name ?? '';
}

function deleteObject() {
    const deleteTarget = getDeleteTarget();
    const objectId = deleteTarget?.id;
    state.modals.objectToDelete.modalContentLoaded = false;

    axios.delete(props.deleteUrl, {
        data: {
            id: objectId,
        }
    }).then((response) => {
        const deletedObject = deleteTarget;
        state.hideModal({ modal: 'objectToDelete' });
        if (response.data.result === 'error') {
            state.flashErrorMessage({ message: response.data.message });
        } else {
            state.flashSuccessMessage({ message: response.data.message });
            emitter.emit('objectDeleted', deletedObject);
        }
    }).catch((error) => {
        console.error(error);
        state.flashErrorMessage({ message: error.response?.data?.message || 'Deletion failed' });
    });
}
</script>

<template>
    <div class="py-1">
        <div class="text-center text-gray-800 dark:text-gray-200 text-lg">
            <p>
                {{ trans('are_you_sure_you_want_to_delete_this_record_2') }}
                <span class="font-semibold text-gray-900 dark:text-gray-100">“{{ getDisplayName() }}”</span>?
            </p>
        </div>
        <div class="mt-8 flex justify-center space-x-4 border-t border-gray-200 dark:border-gray-700 pt-4">
            <Button color="red" @click="deleteObject">
                {{ trans('delete') }}
            </Button>
            <Button color="green" @click="state.hideModal({ modal: 'objectToDelete' })">
                {{ trans('cancel') }}
            </Button>
        </div>
    </div>
</template>
