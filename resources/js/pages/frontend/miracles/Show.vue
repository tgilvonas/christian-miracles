<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    miracle: any;
}>();

const page = usePage();

const currentLocale = computed(() => String(page.props.currentLocale ?? 'en').toLowerCase());

const translation = computed(() => {
    const items = props.miracle?.translations ?? [];
    return (
        items.find((item: any) => item.lang === currentLocale.value) ??
        items.find((item: any) => item.lang === currentLocale.value.split('_')[0]) ??
        items[0] ??
        {}
    );
});

const locationNames = computed(() => {
    return (props.miracle?.locations ?? [])
        .map((location: any) => {
            const locTranslation = (location.translations ?? []).find(
                (item: any) => item.lang === currentLocale.value || item.lang === currentLocale.value.split('_')[0],
            ) ?? (location.translations ?? [])[0];

            return locTranslation?.name ?? location.name ?? null;
        })
        .filter(Boolean)
        .join(', ');
});

const miracleTitle = computed(() => translation.value?.name ?? props.miracle?.name ?? 'Miracle');
const miracleDescription = computed(() => translation.value?.description ?? '');
const textSections = computed(() => props.miracle?.texts ?? []);
</script>

<template>
    <Head :title="miracleTitle" />

    <div class="flex min-h-screen flex-col bg-[#dddddd] p-6 text-[#111111] dark:bg-[#000000] dark:text-[#ffffff] lg:p-8">
        <header class="mx-auto mb-6 w-full max-w-[1200px]">
            <nav class="flex items-center justify-between gap-4">
                <Link href="/" class="inline-flex items-center gap-3">
                    <img src="/images/logo-lt.png" alt="Logo" class="max-h-[6rem]" />
                </Link>
            </nav>
        </header>

        <main class="mx-auto w-full max-w-[1200px]">
            <article class="overflow-hidden rounded bg-white shadow dark:bg-[#111111]">
                <div v-if="props.miracle?.intro_image_url" class="border-b border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-[#0c0c0c]">
                    <img :src="props.miracle.intro_image_url" :alt="miracleTitle" class="h-[320px] w-full object-cover" />
                </div>

                <div class="space-y-6 p-6 md:p-8">
                    <div class="space-y-3">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                            Miracle
                        </div>
                        <h1 class="text-3xl font-bold md:text-5xl">{{ miracleTitle }}</h1>
                        <div v-if="props.miracle?.happened_at" class="text-sm text-gray-600 dark:text-gray-400">
                            {{ props.miracle.happened_at }}
                        </div>
                        <div v-if="locationNames" class="text-sm text-gray-700 dark:text-gray-300">
                            {{ locationNames }}
                        </div>
                    </div>

                    <div
                        v-if="miracleDescription"
                        class="prose prose-sm max-w-none leading-relaxed text-gray-700 dark:prose-invert dark:text-gray-200"
                        v-html="miracleDescription"
                    />

                    <div v-if="textSections.length" class="space-y-8">
                        <section v-for="section in textSections" :key="section.id" class="space-y-3">
                            <h2 v-if="section.title" class="text-2xl font-semibold">{{ section.title }}</h2>

                            <div v-if="section.image_url" class="overflow-hidden rounded border border-gray-200 dark:border-gray-700">
                                <img :src="section.image_url" :alt="section.image_alt || section.title || miracleTitle" class="max-h-[420px] w-full object-cover" />
                            </div>

                            <div
                                v-if="section.text"
                                class="prose prose-sm max-w-none leading-relaxed text-gray-700 dark:prose-invert dark:text-gray-200"
                                v-html="section.text"
                            />
                        </section>
                    </div>
                </div>
            </article>
        </main>
    </div>
</template>
