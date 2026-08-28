<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const currentLocale = computed(() => String(page.props.currentLocale ?? 'en').toLowerCase());
const logoSrc = computed(() => (currentLocale.value.startsWith('en') ? '/images/logo-en.png' : '/images/logo-lt.png'));
</script>

<template>
    <header class="mx-auto mb-6 w-full max-w-[1800px] text-sm not-has-[nav]:hidden">
        <nav class="flex items-center justify gap-4">
            <Link href="/" class="inline-flex items-center">
                <img :src="logoSrc" alt="Logo" class="max-h-[6rem]" />
            </Link>
        </nav>
    </header>
</template>
