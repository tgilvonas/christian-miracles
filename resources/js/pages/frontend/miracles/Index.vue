<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, onMounted, computed } from 'vue';

const page = usePage();
const miracles = ref<any[]>([]);

const currentLocale = computed(() => String(page.props.currentLocale ?? 'en').toLowerCase());
const joinNames = (arr: any[] | undefined) => (arr && arr.length) ? arr.map(a => a.name).join(', ') : '';

const miracleHref = (miracle: any) => {
    const slug = miracle?.slug ?? miracle?.id;
    if (!slug) {
        return '#';
    }

    return route(currentLocale.value.startsWith('lt') ? 'miracles.show_lt' : 'miracles.show_en', { slug });
};

onMounted(async () => {
    try {
        const res = await fetch('/miracles/json');
        if (res.ok) {
            miracles.value = await res.json();
        }
    } catch (e) {
        // ignore for now
    }
});
</script>

<template>
    <div class="flex min-h-screen flex-col bg-[#dddddd] p-6 dark:text-[#ffffff] lg:p-8 dark:bg-[#000000]">
        <header class="mb-6 w-full max-w-[1800px] mx-auto text-sm not-has-[nav]:hidden">
            <nav class="flex items-center justify gap-4">
                <img src="/images/logo-lt.png" alt="Logo" class="max-h-[6rem]" />
            </nav>
        </header>
        <div class="flex w-full items-center justify-center">
            <main class="w-full max-w-[1800px] mx-auto">
                <section class="grid gap-2">
                    <div v-if="!miracles.length">Loading...</div>
                    <Link
                        v-for="m in miracles"
                        :key="m.id"
                        :href="miracleHref(m)"
                        class="block rounded bg-white p-4 shadow transition hover:opacity-90 dark:bg-[#111111]"
                    >
                        <article class="flex items-center gap-4">
                            <img v-if="m.intro_image_url" :src="m.intro_image_url" alt="" class="max-h-[10rem] max-w-[10rem]" />
                            <div>
                                <h2 class="text-xl font-semibold">{{ m.title ?? m.name }}</h2>
                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ m.happened_at }}</div>
                                <div class="text-sm text-gray-700 dark:text-gray-400">{{ joinNames(m.locations) }}</div>
                            </div>
                        </article>
                    </Link>
                </section>
            </main>
        </div>
    </div>
</template>
