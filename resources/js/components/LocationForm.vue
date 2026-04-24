<script setup>
import Button from '@/components/Button.vue';
import { Input } from '@/components/ui/input';
import { trans } from '@/helpers/translator.ts';
import { slugify } from '@/helpers/slugify';
import {ref, reactive, onMounted} from "vue";
import state from '@/state.js';
import axios from "axios";
import { route } from 'ziggy-js';
import emitter from '@/eventBus.js';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    location: {
        type: Object,
        default: null
    }
});

const location = reactive({...props.location});
const formIsValid = ref(true);

onMounted(() => {
    console.log(page.props.locales)
});

// @TODO: implement
function saveLocation() {

}

</script>

<template>
    <div class="pt-1">
        <div class="space-y-5">

            <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 dark:border-gray-700 pt-4">
                <Button
                    color="blue"
                    :disabled="formIsValid === false"
                    @click="saveLocation"
                >
                    {{ trans('save') }}
                </Button>
                <Button
                    color="gray"
                    @click="state.hideModal({ modal: 'location' })"
                >
                    {{ trans('close') }}
                </Button>
            </div>
        </div>
    </div>
</template>
