<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import state from '@/state.js'

const props = defineProps({
    message: {
        type: String,
        default: null,
    },
    type: {
        type: String,
        default: 'success',
        validator: v => ['success', 'error'].includes(v),
    },
    duration: {
        type: Number,
        default: 5000,
    }
});

const wrapperClasses = computed(() => {
    return props.type === 'success'
        ? 'bg-green-600 text-white'
        : 'bg-red-600 text-white';
});

function close() {
    state.messages[props.type].show = false;
}

onMounted(() => {
    if (props.duration > 0) {
        setTimeout(close, props.duration);
    }
});

</script>

<template>
    <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
    >
        <div
            v-if="state.messages[type].show"
            class="fixed top-5 right-5 z-6000 max-w-sm rounded-xl px-4 py-3 shadow-lg flex items-start justify-between gap-4"
            :class="wrapperClasses"
        >
            <div class="text-sm font-medium">
                {{ state.messages[type].messageString }}
            </div>
            <button class="text-white" @click="close">
                ✕
            </button>
        </div>
    </transition>
</template>
