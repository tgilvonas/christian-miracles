<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { trans } from '@/helpers/translator';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('miracles'),
        href: route('admin.miracles.index'),
    },
];

</script>

<template>
    <Head :title="trans('miracles')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-3">
            <div>To be developed here</div>
        </div>
    </AppLayout>
</template>
