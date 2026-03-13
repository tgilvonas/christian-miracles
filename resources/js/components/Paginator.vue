<template>
    <nav
        v-if="pagination && pagination.length > 3"
        class="flex items-left justify-left gap-2 mt-6"
    >
        <Button
            v-for="(link, index) in pagination"
            :key="index"
            :disabled="!link.url || link.active"
            @click="goToPage(link.url)"
            v-html="link.label"
            class="min-w-[40px]"
        />
    </nav>
</template>

<script setup>
import Button from '@/components/Button.vue'
import emitter from '@/eventBus.js'

function goToPage(url) {
    const urlObject = new URL(url);
    const page = urlObject.searchParams.get('page');
    if (page !== null) {
        emitter.emit('paginatorClicked', { page });
    }
}

const props = defineProps({
    pagination: {
        type: Object,
        required: true
    }
})

</script>
