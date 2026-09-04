<script setup lang="ts">
import WebsiteHeader from '@/components/WebsiteHeader.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, onMounted, computed, watch } from 'vue';
import { trans } from '@/helpers/translator';

const page = usePage();
const miracles = ref<any[]>([]);
const locations = ref<any[]>([]);
const search = ref<string>('');
const selectedLocation = ref<string | number | null>(null);
const loading = ref<boolean>(false);

const currentLocale = computed(() => String(page.props.currentLocale ?? 'en').toLowerCase());
const joinNames = (arr: any[] | undefined) => (arr && arr.length) ? arr.map(a => a.name).join(', ') : '';

const miracleHref = (miracle: any) => {
    const slug = miracle?.slug ?? miracle?.id;
    if (!slug) {
        return '#';
    }

    return route(currentLocale.value.startsWith('lt') ? 'miracles.show_lt' : 'miracles.show_en', { slug });
};

let fetchTimer: number | undefined;

const fetchMiracles = async () => {
    loading.value = true;
    try {
        const params = new URLSearchParams();
        if (search.value) params.append('q', String(search.value));
        if (selectedLocation.value) params.append('location_id', String(selectedLocation.value));

        const url = '/miracles/json' + (params.toString() ? `?${params.toString()}` : '');
        const res = await fetch(url);
        if (res.ok) {
            const data = await res.json();
            miracles.value = data.miracles ?? data;
            if (data.locations) locations.value = data.locations;
        }
    } catch (e) {
        // ignore for now
    }
    loading.value = false;
};

onMounted(fetchMiracles);

watch([search, selectedLocation], () => {
    if (fetchTimer) window.clearTimeout(fetchTimer);
    // debounce
    // @ts-ignore -- browser timeout id
    fetchTimer = window.setTimeout(() => fetchMiracles(), 350);
});
</script>

<template>
    <div class="flex min-h-screen flex-col bg-[#dddddd] p-6 dark:text-[#ffffff] lg:p-8 dark:bg-[#000000]">
        <WebsiteHeader />
        <div class="flex w-full items-center justify-center">
            <main class="w-full max-w-[1800px] mx-auto">
                <div class="mb-4">
                    <div class="flex gap-2">
                        <input
                            v-model="search"
                            type="search"
                            :placeholder="trans('search')"
                            class="w-full rounded border px-3 py-2 bg-white text-gray-900 placeholder-gray-500 border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 dark:bg-[#0b0b0b] dark:text-gray-100 dark:placeholder-gray-400 dark:border-gray-700 dark:focus:ring-blue-600"
                        />

                        <select
                            v-model="selectedLocation"
                            class="rounded border px-3 py-2 bg-white text-gray-900 border-gray-300 dark:bg-[#0b0b0b] dark:text-gray-100 dark:border-gray-700"
                        >
                            <option :value="null">{{ trans('all_locations') }}</option>
                            <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                        </select>

                        <button
                            v-if="(search || selectedLocation)"
                            @click="(search=''), (selectedLocation=null), fetchMiracles()"
                            class="rounded px-3 bg-gray-200 text-gray-900 hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-100 hover:dark:bg-gray-700"
                        >
                            {{ trans('clear') }}
                        </button>
                    </div>
                </div>

                <section class="grid gap-2">
                    <div v-if="loading">Loading...</div>
                    <div v-else-if="!miracles.length">No results found.</div>
                    <Link
                        v-for="m in miracles"
                        :key="m.id"
                        :href="miracleHref(m)"
                        class="block rounded bg-white p-4 shadow transition hover:opacity-95 dark:bg-[#0f1115] text-gray-900 dark:text-gray-100 border border-transparent dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-[#0b0b0b]"
                    >
                        <article class="flex items-center gap-4">
                            <img v-if="m.intro_image_url" :src="m.intro_image_url" alt="" class="max-h-[10rem] max-w-[10rem] object-cover rounded bg-gray-100 dark:bg-gray-800" />
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ m.title ?? m.name }}</h2>
                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ m.happened_at }}</div>
                                <div class="text-sm text-gray-700 dark:text-gray-300">{{ joinNames(m.locations) }}</div>
                            </div>
                        </article>
                    </Link>
                </section>
            </main>
        </div>
    </div>
</template>
