import { reactive } from 'vue';

const modals= reactive({
    miracle: {
        zIndex: 3100,
        show: false,
        objectInModal: null,
        modalContentLoaded: false
    },
    saint: {
        zIndex: 3200,
        show: false,
        objectInModal: null,
        modalContentLoaded: false
    },
    location: {
        zIndex: 3300,
        show: false,
        objectInModal: null,
        modalContentLoaded: false
    },
    socialStatus: {
        zIndex: 3400,
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
        modals[data.modal]['show'] = true;
        modals[data.modal]['objectInModal'] = data.objectInModal;
    },
    hideModal (data) {
        modals[data.modal]['show'] = false;
        modals[data.modal]['modalContentLoaded'] = false;
        modals[data.modal]['objectInModal'] = false;
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
