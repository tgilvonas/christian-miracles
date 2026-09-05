<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import { trans } from '@/helpers/translator';
import LocaleSwitcher from '@/components/LocaleSwitcher.vue';

const page = usePage();

const currentLocale = computed(() => String(page.props.currentLocale ?? 'en').toLowerCase());
const currentLocaleValue = computed(() => currentLocale.value);
const locales = computed(() => (page.props.locales ?? {}));
const logoSrc = computed(() => (currentLocaleValue.value === 'en' ? '/images/logo-en.png' : '/images/logo-lt.png'));

const miraclesLink = computed(() => route('home'));
const saintsLink = computed(() => route(currentLocale.value.startsWith('lt') ? 'saints_index_lt' : 'saints_index_en'));

const miraclesLabel = computed(() => trans('miracles'));
const saintsLabel = computed(() => trans('saints'));
</script>

<template>
    <header class="mx-auto mb-6 w-full max-w-[1800px] text-sm not-has-[nav]:hidden">
        <nav class="flex flex-col items-start gap-2">
            <div class="inline-flex items-center">
                <Link :href="miraclesLink" class="inline-flex items-center">
                    <img :src="logoSrc" alt="Logo" class="max-h-[6rem]" />
                </Link>
            </div>

            <div class="flex gap-4 mt-2 items-center">
                <div class="flex gap-4">
                    <Link :href="miraclesLink" class="font-medium text-gray-700 dark:text-gray-200">{{ miraclesLabel }}</Link>
                    <Link :href="saintsLink" class="font-medium text-gray-700 dark:text-gray-200">{{ saintsLabel }}</Link>
                </div>

                <div class="ml-4 flex items-center gap-2">
                    <span class="font-medium text-gray-700 dark:text-gray-200">{{ trans('language') }}</span>
                    <LocaleSwitcher :locales="locales" :currentLocale="currentLocaleValue" />
                </div>
            </div>
        </nav>
    </header>
</template>
