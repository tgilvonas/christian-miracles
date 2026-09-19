<script setup>
import Button from '@/components/Button.vue';
import Tabs from '@/components/Tabs.vue';
import { trans } from '@/helpers/translator.ts';
import { reactive, ref, watch } from 'vue';
import state from '@/state.js';
import axios from 'axios';
import { route } from 'ziggy-js';
import eventBus from '@/eventBus.js';

const socialStatus = reactive({
    translations: {},
});
const formIsValid = ref(true);
const isLoading = ref(false);

watch(
    () => state.modals.socialStatus.objectId,
    () => {
        if (!state.modals.socialStatus.show) {
            return;
        }

        loadSocialStatusForm();
    },
    { immediate: true }
);

function loadSocialStatusForm() {
    isLoading.value = true;

    const objectId = state.modals.socialStatus.objectId;
    const requestUrl = objectId
        ? route('admin.social_statuses.edit', { socialStatusId: objectId })
        : route('admin.social_statuses.create');

    axios.get(requestUrl)
        .then((response) => {
            Object.keys(socialStatus).forEach((key) => {
                delete socialStatus[key];
            });

            Object.assign(socialStatus, response.data.social_status ?? {}, {
                translations: response.data.translations ?? {},
            });
            state.modals.socialStatus.modalContentLoaded = true;
        })
        .finally(() => {
            isLoading.value = false;
        });
}

function saveSocialStatus() {
    const { translations, ...socialStatusData } = socialStatus;
    const payload = {
        social_status: socialStatusData,
        translations: Object.fromEntries(
            Object.entries(translations || {}).map(([locale, translation]) => [locale, { ...(translation || {}) }])
        ),
    };

    isLoading.value = true;

    const saveUrl = state.modals.socialStatus.objectId
        ? route('admin.social_statuses.save', { socialStatusId: state.modals.socialStatus.objectId })
        : route('admin.social_statuses.save');

    axios.post(saveUrl, payload)
        .then((response) => {
            state.flashSuccessMessage({
                message: response.data.message || 'Social status saved successfully.',
            });
            eventBus.emit('socialStatusSaved');
            state.hideModal({ modal: 'socialStatus' });
        })
        .catch((error) => {
            state.flashErrorMessage({
                message: error.response?.data?.message || 'Unable to save social status.',
            });
        })
        .finally(() => {
            isLoading.value = false;
        });
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
                    <template v-if="socialStatus.translations && socialStatus.translations[activeTab]">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                                {{ trans('title') }}
                            </label>
                            <input
                                v-model="socialStatus.translations[activeTab].name"
                                :placeholder="trans('title')"
                                class="border border-gray-300 dark:border-gray-600 rounded-md p-2 w-full"
                            />
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                                {{ trans('slug') }}
                            </label>
                            <div class="flex items-center gap-2">
                                /<input
                                    v-model="socialStatus.translations[activeTab].slug"
                                    :placeholder="trans('slug')"
                                    class="border border-gray-300 dark:border-gray-600 rounded-md p-2 w-full"
                                />
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                                Meta Description
                            </label>
                            <textarea
                                v-model="socialStatus.translations[activeTab].meta_description"
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
                                v-model="socialStatus.translations[activeTab].meta_keywords"
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
                    @click="saveSocialStatus"
                >
                    {{ trans('save') }}
                </Button>
                <Button
                    color="gray"
                    @click="state.hideModal({ modal: 'socialStatus' })"
                >
                    {{ trans('close') }}
                </Button>
            </div>
        </div>
    </div>
</template>
