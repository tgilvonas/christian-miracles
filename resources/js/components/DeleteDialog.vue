<script setup lang="ts">
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

function deleteObject() {
    const deleteTarget = (state.modals.objectToDelete as Record<string, any>)?.objectInModal as Record<string, any> | null;
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
                <span class="font-semibold text-gray-900 dark:text-gray-100">“{{ (state.modals.objectToDelete as Record<string, any>)?.objectInModal?.title }}”</span>?
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
