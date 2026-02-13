<template>
    <button
        :class="[
      baseClasses,
      colorClasses,
      (disabled || loading)
        ? 'opacity-60 cursor-not-allowed'
        : 'hover:brightness-110',
    ]"
        :disabled="disabled || loading"
    >
        <slot />
    </button>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
    color?: 'blue' | 'red' | 'green' | 'gray'
        disabled?: boolean
    loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    color: 'blue',
    disabled: false,
    loading: false,
});

const baseClasses = `p-2 mr-2 rounded-lg font-semibold text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-150 cursor-pointer`

const colorClasses = computed(() => {
    switch (props.color) {
        case 'red':
            return 'bg-red-600 hover:bg-red-700 focus:ring-red-300'
        case 'green':
            return 'bg-green-600 hover:bg-green-700 focus:ring-green-300'
        case 'gray':
            return 'bg-gray-600 hover:bg-gray-700 focus:ring-gray-300'
        default:
            return 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-300'
    }
})
</script>
