<script setup>
import Button from '@/components/Button.vue';
import Tabs from '@/components/Tabs.vue';
import { trans } from '@/helpers/translator.ts';
import { onMounted, reactive, ref } from 'vue';
import state from '@/state.js';
import axios from 'axios';
import { route } from 'ziggy-js';

const location = reactive({
    translations: {},
});
const formIsValid = ref(true);
const isLoading = ref(false);

onMounted(() => {
    if (state.modals.location.objectId === null) {
        loadLocationForm();
    }
});

function loadLocationForm() {
    isLoading.value = true;

    axios.get(route('admin.locations.create'))
        .then((response) => {
            Object.assign(location, response.data.location ?? {}, {
                translations: response.data.translations ?? {},
            });
            state.modals.location.modalContentLoaded = true;
        })
        .finally(() => {
            isLoading.value = false;
        });
}

function saveLocation() {
    // @todo: implement persistence
}
</script>

<template>
    <div class="pt-1">
        <div class="space-y-5">
            <div v-if="isLoading" class="text-sm text-gray-500">
                Loading...
            </div>

            <Tabs v-else>
                <template #default="{ activeTab }">
                    <template v-if="location.translations && location.translations[activeTab]">
                        <input
                            v-model="location.translations[activeTab].title"
                            placeholder="Title"
                            class="border p-2 w-full mb-2"
                        />

                        <input
                            v-model="location.translations[activeTab].slug"
                            placeholder="Slug"
                            class="border p-2 w-full"
                        />
                    </template>
                </template>
            </Tabs>

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
