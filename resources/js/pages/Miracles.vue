<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col bg-[#dddddd] p-6 dark:text-[#ffffff] lg:p-8 dark:bg-[#000000]">
        <header class="mb-6 w-full max-w-[1800px] text-sm not-has-[nav]:hidden">
            <nav class="flex items-center justify gap-4">
                <img src="/images/logo-lt.png" alt="Logo" class="max-h-[5rem]" />
            </nav>
        </header>
        <div class="flex w-full items-center justify-center">
            <main class="w-full max-w-[1800px]">
                <h1 class="mb-1 text-2xl mb-2">Krikščionių šventųjų ir jų stebuklų svetainė</h1>
                <p class="text-lg">Vyksta kūrimo darbai</p>
            </main>
        </div>
    </div>
</template>
