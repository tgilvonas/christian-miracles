<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { trans } from '@/helpers/translator'
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js'
import {ref, onMounted, onBeforeUnmount} from "vue";
import axios from "axios";

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
            <button class="px-3 py-2 bg-green-600 text-white font-medium rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-300">+ {{ trans('create_new') }}</button>
        </div>
    </AppLayout>
</template>
