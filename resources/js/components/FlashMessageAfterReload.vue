<script setup>
import { ref, computed, onMounted, watch, onBeforeUnmount } from 'vue';

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
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:show', 'close']);

const wrapperClasses = computed(() => {
    return props.type === 'success'
        ? 'bg-green-600 text-white'
        : 'bg-red-600 text-white';
});

let timeoutId = null;

const localShow = ref(props.show);

function close() {
    localShow.value = false;
    emit('update:show', false);
}

onMounted(() => {
    if (props.duration > 0 && localShow.value) {
        timeoutId = setTimeout(close, props.duration);
    }
});

watch(
    () => props.show,
    (value) => {
        localShow.value = value;

        if (timeoutId) {
            clearTimeout(timeoutId);
            timeoutId = null;
        }

        if (value && props.duration > 0) {
            timeoutId = setTimeout(close, props.duration);
        }
    }
);

watch(
    () => localShow.value,
    (value) => {
        if (!value && timeoutId) {
            clearTimeout(timeoutId);
            timeoutId = null;
        }
    }
);

onBeforeUnmount(() => {
    if (timeoutId) {
        clearTimeout(timeoutId);
        timeoutId = null;
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
            v-if="localShow"
            class="fixed top-5 right-5 z-6000 max-w-sm rounded-xl px-4 py-3 shadow-lg flex items-start justify-between gap-4"
            :class="wrapperClasses"
        >
            <div class="text-sm font-medium">
                {{ message }}
            </div>
            <button class="text-white" @click="close">
                ✕
            </button>
        </div>
    </transition>
</template>
