<template>
    <div class="o-view-edit-general">
        <FormWrapper
            :form="form"
        >
            <SettingsHeaderLine class="mb-8">
                <template
                    #header
                >
                    View name
                </template>

                <div class="max-w-sm">
                    <InputBox
                        formField="name"
                        placeholder="Your view's name"
                        bgColor="gray"
                    >
                    </InputBox>
                </div>

            </SettingsHeaderLine>

            <SettingsHeaderLine>
                <template
                    #header
                >
                    View type
                </template>
                <div
                    class="bg-primary-100 rounded-md inline-block py-1 px-2 font-semibold"
                >
                    {{ viewTypeName }}
                </div>
            </SettingsHeaderLine>

            <SaveButtonSticky
                :disabled="!canSave || processingSomething"
                :pulse="true"
                @click.stop="saveGeneral"
            >
            </SaveButtonSticky>
        </FormWrapper>

        <div
            class="rounded-xl p-6 bg-cm-100 mt-3"
        >
            <SettingsHeaderLine>
                <template
                    #header
                >
                    <template
                        v-if="!isDefaultView"
                    >
                        Set <span class="font-bold text-primary-600">{{ viewName }}</span>
                        as the default view on <span class="font-bold text-primary-600">{{ pageName }}</span>
                    </template>

                    <template
                        v-else
                    >
                        <div class="flex items-center">
                            <i class="fa-solid fa-circle-check text-primary-600 text-xl mr-2">
                            </i>
                            <p>
                                <span class="font-bold text-primary-600">{{ viewName }}</span>
                                is the default view on
                                <span class="font-bold text-primary-600">{{ pageName }}</span>
                            </p>
                        </div>
                    </template>
                </template>

                <button
                    v-if="!isDefaultView"
                    class="button button-primary"
                    :class="{ unclickable: processingSomething }"
                    type="button"
                    :disabled="processingSomething"
                    @click="saveDefaultView"
                >
                    Set as default
                </button>

            </SettingsHeaderLine>
        </div>

        <div
            v-if="!isDefaultView"
            class="rounded-xl p-6 bg-cm-100 mt-3"
        >
            <SettingsHeaderLine>
                <template
                    #header
                >
                    Delete <span class="font-bold text-primary-600">{{ viewName }}</span>
                </template>

                <template
                    #description
                >
                    Do you permanently wish to delete this view? This action cannot be undone.
                </template>

                <button
                    v-t="'common.delete'"
                    class="button button-peach"
                    :class="{ unclickable: processingSomething }"
                    type="button"
                    :disabled="processingSomething"
                    @click="deleteView"
                >
                </button>

            </SettingsHeaderLine>
        </div>
    </div>
</template>

<script>

import interactsWithFormCanSave from '@/vue-mixins/common/interactsWithFormCanSave.js';
import {
    updatePageView,
    deletePageView,
    updatePageDesign,
} from '@/core/repositories/pageRepository.js';

export default {
    name: 'ViewEditGeneral',
    components: {

    },
    mixins: [
        interactsWithFormCanSave,
    ],
    props: {
        page: {
            type: Object,
            required: true,
        },
        view: {
            type: Object,
            required: true,
        },
        viewName: {
            type: String,
            required: true,
        },
    },
    emits: [
        'closeModal',
    ],
    data() {
        return {
            form: this.$apolloForm(() => {
                return {
                    ...this.view,
                    name: this.viewName,
                };
            }),
            // Set as the current view for saving
            defaultViewForm: this.$apolloForm(() => {
                return {
                    defaultView: this.view.id,
                };
            }),
            requiredFields: [
                'name',
            ],
            processingDelete: false,
            processingDefaultView: false,
        };
    },
    computed: {
        viewTypeName() {
            return this.$t(`views.dashboard.${_.camelCase(this.view.viewType)}`);
        },
        checkerForm() {
            return this.form;
        },
        checkerOriginal() {
            return this.view;
        },
        processingSomething() {
            return this.processingDefaultView || this.processingDelete;
        },
        isDefaultView() {
            return this.view.id === this.page.design?.defaultView;
        },
        pageName() {
            return this.page.name;
        },
    },
    methods: {
        async saveGeneral() {
            await updatePageView(this.form, this.page);
            this.$saveFeedback();
        },
        async saveDefaultView() {
            this.processingDefaultView = true;
            try {
                await updatePageDesign(this.defaultViewForm, this.page);
                this.$saveFeedback();
            } finally {
                this.processingDefaultView = false;
            }
        },
        async deleteView() {
            this.processingDelete = true;
            try {
                await deletePageView(this.view.id, this.page);
                this.$emit('closeModal');
            } finally {
                this.processingDelete = false;
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-view-edit-general {

} */

</style>
