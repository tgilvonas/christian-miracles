<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { trans } from '@/helpers/translator'
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js'
import {ref, onMounted, onBeforeUnmount} from "vue";
import axios from "axios";
import Button from '@/components/Button.vue';
import FlashMessage from "@/components/FlashMessage.vue";
import Modal from "@/components/Modal.vue";
import state from '@/state.js';
import LocationForm from "@/components/LocationForm.vue";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('locations'),
        href: route('admin.locations.index'),
    },
];

const loading = ref(false);
const locations = ref([]);
const pagination = ref([]);
const searchText = ref('');

onMounted(() => {
    getLocationsList(1);
});
onBeforeUnmount(() => {
    // @todo
});

function getLocationsList(page: number) {
    loading.value = true;
    axios.get(route('admin.locations.json_list'), {
        params: {
            paginate_by: 10
        }
    }).then(function(response){
        loading.value = false;
        // @todo
    })
}

</script>

<template>
    <Head :title="trans('locations')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-3">
            <FlashMessage type="success"></FlashMessage>
            <FlashMessage type="error"></FlashMessage>
            <Modal modal-name="location" size="lg">
                <template #modal_title>
                    {{ trans('location') }}
                </template>
                <template #content>
                    <div>
                        <LocationForm :location="state.modals.location.objectInModal" />
                    </div>
                </template>
            </Modal>
            <Button @click="state.callModal({modal: 'location', objectInModal: {}})" color="green">
                {{ trans('create_new') }}
            </Button>
        </div>
    </AppLayout>
</template>
