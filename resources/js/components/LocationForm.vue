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
                {{ trans('loading') }}
            </div>

            <Tabs v-else>
                <template #default="{ activeTab }">
                    <template v-if="location.translations && location.translations[activeTab]">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                                {{ trans('title') }}
                            </label>
                            <input
                                v-model="location.translations[activeTab].name"
                                :placeholder="trans('title')"
                                class="border border-gray-300 dark:border-gray-600 rounded-md p-2 w-full"
                            />
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                                {{ trans('slug') }}
                            </label>
                            <input
                                v-model="location.translations[activeTab].slug"
                                :placeholder="trans('slug')"
                                class="border border-gray-300 dark:border-gray-600 rounded-md p-2 w-full"
                            />
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                                Meta Description
                            </label>
                            <textarea
                                v-model="location.translations[activeTab].meta_description"
                                placeholder="Meta Description"
                                rows="3"
                                class="border border-gray-300 dark:border-gray-600 rounded-md p-2 w-full"
                            />
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                                Meta Keywords
                            </label>
                            <textarea
                                v-model="location.translations[activeTab].meta_keywords"
                                placeholder="Meta Keywords"
                                rows="3"
                                class="border border-gray-300 dark:border-gray-600 rounded-md p-2 w-full"
                            />
                        </div>
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
