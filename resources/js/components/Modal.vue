<template>
    <div
        v-if="state.modals[modalName].show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
        :style="{ zIndex: state.modals[modalName].zIndex }"
    >
        <div
            :class="[
                'bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full p-6 relative',
                sizeClass
            ]"
        >
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                    <slot name="modal_title"></slot>
                </h2>
                <button
                    @click="state.hideModal({ modal: modalName })"
                    class="text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 text-xl leading-none"
                >
                    ✕
                </button>
            </div>
            <div class="modal-content">
                <slot name="content"></slot>
            </div>
        </div>
    </div>
</template>

<script setup>

import { computed } from 'vue'
import state from '@/state.js'

const props = defineProps({
    modalName: {
        type: String,
        required: true,
    },
    size: {
        type: String,
        default: 'sm',
        validator: (v) => ['sm', 'lg'].includes(v),
    },
});

const sizeClass = computed(() => {
    return props.size === 'lg'
        ? 'max-w-6xl'
        : 'max-w-lg'
});

</script>
