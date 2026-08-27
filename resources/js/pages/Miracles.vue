<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const miracles = ref<any[]>([]);

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
                    <article v-for="m in miracles" :key="m.id" class="flex items-center gap-4 rounded bg-white p-4 shadow dark:bg-[#111111]">
                        <img v-if="m.intro_image_url" :src="m.intro_image_url" alt="" class="h-24 w-24 object-cover" />
                        <div>
                            <h2 class="text-xl font-semibold">{{ m.title ?? m.name }}</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ m.happened_at }}</p>
                        </div>
                    </article>
                </section>
            </main>
        </div>
    </div>
</template>
