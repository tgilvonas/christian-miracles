import { reactive } from 'vue';

const modals = reactive({
    location: {
        zIndex: 3300,
        show: false,
        objectId: null,
        modalContentLoaded: false
    },
    socialStatus: {
        zIndex: 3400,
        show: false,
        objectId: null,
        modalContentLoaded: false
    },
    objectToDelete: {
        zIndex: 5000,
        show: false,
        objectInModal: null,
        modalContentLoaded: false
    }
});

const messages = reactive({
    success: {
        show: false,
        messageString: '',
    },
    error: {
        show: false,
        messageString: '',
    }
});

export default {
    modals: modals,
    messages: messages,
    savedObject: reactive({}),
    callModal (data) {
        const targetModal = modals[data.modal];
        if (!targetModal) {
            return;
        }

        targetModal.show = true;
        targetModal.objectId = data.objectId ?? null;
        targetModal.modalContentLoaded = false;
        targetModal.objectInModal = data.objectInModal ?? null;
    },
    hideModal (data) {
        const targetModal = modals[data.modal];
        if (!targetModal) {
            return;
        }

        targetModal.show = false;
        targetModal.modalContentLoaded = false;
        targetModal.objectId = false;
        targetModal.objectInModal = null;
    },
    flashSuccessMessage (data) {
        messages.success.messageString = data.message;
        messages.success.show = true;
    },
    hideSuccessMessage () {
        messages.success.show = false;
    },
    flashErrorMessage (data) {
        messages.error.messageString = data.message;
        messages.error.show = true;
    },
    hideErrorMessage () {
        messages.error.show = false;
    },
}
