<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { trans } from '@/helpers/translator'
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('locations'),
        href: route('admin.locations.index'),
    },
];

</script>

<template>
    <Head :title="trans('locations')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-3">
            <button class="px-3 py-2 bg-green-600 text-white font-medium rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-300">+ {{ trans('create_new') }}</button>
        </div>
    </AppLayout>
</template>
