<template>
    <div class="o-view-edit-new">
        <FormWrapper
            :form="form"
            @submit="saveView"
        >
            <SettingsHeaderLine
                class="mb-10"
            >
                <template
                    #header
                >
                    View name
                </template>

                <div class="max-w-sm">
                    <InputBox
                        ref="nameInput"
                        formField="name"
                        placeholder="Your new view's name"
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

                <div>
                    <CheckHolder
                        v-for="option in dashboardViews"
                        :key="option.viewType"
                        :modelValue="form.viewType"
                        type="radio"
                        :val="option.viewType"
                        holderClasses="my-1 items-center"
                        @update:modelValue="updateType(option, $event)"
                    >
                        <div class="flex items-center">
                            <i
                                class="fal fa-fw mr-2 text-primary-500"
                                :class="option.symbol"
                            >
                            </i>
                            {{ optionName(option.viewType) }}
                        </div>
                    </CheckHolder>
                </div>
            </SettingsHeaderLine>

            <SaveButtonSticky
                :disabled="!canSave"
                :pulse="true"
                type="submit"
            >
            </SaveButtonSticky>
        </FormWrapper>
    </div>
</template>

<script>

import interactsWithFormCanSave from '@/vue-mixins/common/interactsWithFormCanSave.js';

import { dashboardViews } from '@/core/display/fullViews.js';
import { updatePageView } from '@/core/repositories/pageRepository.js';

export default {
    name: 'ViewEditNew',
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
    },
    emits: ['viewCreated'],
    data() {
        return {
            form: this.$apolloForm(() => {
                return {
                    name: '',
                    symbol: 'fa-rows',
                    viewType: 'LINE',
                    categoryType: 'DASHBOARD',
                };
            }),
            requiredFields: [
                'name',
            ],
        };
    },
    computed: {
        checkerForm() {
            return this.form;
        },
        checkerOriginal() {
            return false;
        },
    },
    methods: {
        optionName(val) {
            return this.$t(`views.dashboard.${_.camelCase(val)}`);
        },
        async saveView() {
            const view = await updatePageView(this.form, this.page);
            this.$saveFeedback();
            this.$emit('viewCreated', view);
        },
        updateType(option, type) {
            this.form.viewType = type;
            this.form.symbol = option.symbol;
        },
    },
    created() {
        this.dashboardViews = dashboardViews;
    },
    mounted() {
        this.$refs.nameInput.focus();
    },
};
</script>

<style scoped>

/*.o-view-edit-new {

} */

</style>
