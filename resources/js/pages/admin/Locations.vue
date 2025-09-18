<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { trans } from '@/helpers/translator'
</script>

<template>
    <Head :title="trans('locations')" />
    <AppLayout>
        <div class="p-3">
            <h2 class="text-lg">{{ trans('locations') }}</h2>
        </div>
    </AppLayout>
</template>
