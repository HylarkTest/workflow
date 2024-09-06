<template>
    <Modal
        class="o-page-create-modal"
        containerClass="w-600p p-4"
        :header="modalHeader"
        :description="modalDescription"
        v-bind="$attrs"
        @closeModal="closeModal"
    >
        <div
            class="px-4 pb-8 mt-8"
            :class="{ unclickable: processing }"
        >
            <FormWrapper
                :form="pageForm"
            >
                <SettingsHeaderLine
                    class="mb-8"
                >
                    <template
                        #header
                    >
                        Name
                    </template>

                    <div>
                        <InputBox
                            ref="nameInput"
                            v-model="pageForm.name"
                            bgColor="gray"
                            placeholder="Page name"
                        >
                        </InputBox>
                    </div>
                </SettingsHeaderLine>

                <SettingsHeaderLine>
                    <template
                        #header
                    >
                        Icon
                    </template>

                    <IconEdit
                        v-model:symbol="pageForm.symbol"
                    >
                    </IconEdit>
                </SettingsHeaderLine>

                <SaveButtonSticky
                    :disabled="saveOff"
                    @click.stop="savePage"
                >
                </SaveButtonSticky>
            </FormWrapper>
        </div>
    </Modal>
</template>

<script>

import IconEdit from '@/components/assets/IconEdit.vue';

import providesPageGeneralForm from '@/vue-mixins/customizations/providesPageGeneralForm.js';
import interactsWithFormCanSave from '@/vue-mixins/common/interactsWithFormCanSave.js';

import { createPage, updateMappingPage } from '@/core/repositories/pageRepository.js';

import popularIcons from '@/core/data/popularIcons.js';

export default {
    name: 'PageCreateModal',
    components: {
        IconEdit,
    },
    mixins: [
        providesPageGeneralForm,
        interactsWithFormCanSave,
    ],
    props: {
        propPageType: {
            type: String,
            required: true,
        },
        propSymbol: {
            type: String,
            default: '',
        },
        modalHeader: {
            type: String,
            required: true,
        },
        modalDescription: {
            type: String,
            default: '',
        },
        propPageName: {
            type: String,
            default: '',
        },
        propMapping: {
            type: [Object, null],
            default: null,
        },
        item: {
            type: [Object, null],
            default: null,
        },
        behaviors: {
            type: [Array, null],
            default: null,
            validator: (val) => {
                if (val === null) {
                    return true;
                }
                return val.every((behavior) => {
                    return ['ADD_RECORD'].includes(behavior);
                });
            },
        },
    },
    emits: [
        'closeModal',
    ],
    data() {
        return {
            processing: false,
            requiredFields: [
                'name',
                'symbol',
            ],
            newPage: null,
            pageForm: this.$apolloForm(() => {
                return {
                    type: this.propPageType || null,
                    symbol: this.propSymbol || this.getDefaultIcon(),
                    name: this.propPageName || '',
                    mapping: this.propMapping?.id || null,
                };
            }),
        };
    },
    computed: {
        saveOff() {
            return this.processing || !this.canSave;
        },
        checkerForm() {
            return this.pageForm;
        },
        space() {
            return this.propMapping.space;
        },
    },
    methods: {
        async savePage() {
            this.processing = true;
            try {
                const response = await createPage(this.pageForm, this.space);
                this.newPage = response.page;

                if (this.behaviors) {
                    this.behaviors.forEach((behavior) => {
                        const pascalBehavior = _.pascalCase(behavior);
                        const handleMethod = `handle${pascalBehavior}`;
                        // handleAddRecord
                        this[handleMethod]();
                    });
                } else {
                    this.$saveFeedback({
                        customHeaderPath: {
                            path: 'feedback.records.pageCreation.header',
                            args: {
                                pageName: this.newPage.name,
                            },
                        },
                        hideMessage: true,
                        isHtml: true,
                    }, 5000);
                }
                this.closeModal();
            } finally {
                this.processing = false;
            }
        },
        closeModal() {
            this.$emit('closeModal');
        },
        async handleAddRecord() {
            await updateMappingPage(this.$apolloForm({
                id: this.newPage.id,
                entityId: this.item.id,
            }), this.newPage);

            this.$saveFeedback({
                customHeaderPath: {
                    path: 'feedback.records.pageCreation.header',
                    args: {
                        pageName: this.newPage.name,
                    },
                },
                customMessagePath: {
                    path: 'feedback.records.pageCreationAssociation.message',
                    args: {
                        recordName: this.item.name,
                        pageName: this.newPage.name,
                    },
                },
                isHtml: true,
            }, 10000);
        },
        getDefaultIcon() {
            const count = popularIcons.length;
            const randomIndex = Math.floor(Math.random() * count);
            return popularIcons[randomIndex];
        },
    },
    created() {
    },
    mounted() {
        // this.$refs.nameInput.$refs.input.select();
    },
};
</script>

<style scoped>

/*.o-page-create-modal {

} */

</style>
