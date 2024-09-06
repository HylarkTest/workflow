<template>
    <Modal
        class="o-duplicate-item-modal"
        containerClass="w-[400px] p-4"
        :header="modalHeader"
        :description="$t('functionality.duplication.items.description')"
        v-bind="$attrs"
        @closeModal="closeModal"
    >
        <div
            :class="{ unclickable: processing }"
        >
            <div>
                <div
                    v-for="option in filteredOptions"
                    :key="option"
                    class="mb-2.5 last:mb-0"
                >
                    <div
                        v-if="option.subOptions"
                    >
                        <p class="font-semibold mb-1">
                            {{ getLabel(option.val) }}
                        </p>
                        <div class="ml-8">
                            <div
                                v-for="subOption in option.subOptions"
                                :key="subOption.val"
                                class="mb-1.5 last:mb-0"
                            >
                                <CheckHolder
                                    v-model="duplicationAspects"
                                    :val="subOption.val"
                                >
                                    {{ getLabel(subOption.val) }}
                                </CheckHolder>
                            </div>
                        </div>
                    </div>

                    <CheckHolder
                        v-else
                        v-model="duplicationAspects"
                        :val="option.val"
                        :disabled="option.val === 'GENERAL_INFO'"
                    >
                        {{ getLabel(option.val) }}
                    </CheckHolder>

                </div>
            </div>

            <SaveButtonSticky
                textPath="common.createCopy"
                :disabled="processing"
                @click.stop="duplicateItem"
            >
            </SaveButtonSticky>
        </div>
    </Modal>
</template>

<script>

import { isActiveBaseCollaborative } from '@/core/repositories/baseRepository.js';

const options = {
    RECORD: [
        {
            val: 'GENERAL_INFO',
        },
        {
            val: 'MARKERS',
        },
        {
            val: 'RELATIONSHIPS',
        },
        {
            val: 'ASSIGNEES',
            isHiddenCondition: 'isPersonalBase',
        },
        // Removing features for now and will add them back with the option to
        // clone the association or the associated item. Right now it is not clear
        // to the user how it is being cloned.
        // {
        //     val: 'FEATURES',
        //     subOptions: [
        //         {
        //             val: 'TODOS',
        //         },
        //         {
        //             val: 'EVENTS',
        //         },
        //         {
        //             val: 'DOCUMENTS',
        //         },
        //         {
        //             val: 'LINKS',
        //         },
        //         {
        //             val: 'PINS',
        //         },
        //         {
        //             val: 'NOTES',
        //         },
        //         {
        //             val: 'TIMEKEEPER',
        //         },
        //     ],
        // },
    ],
    FEATURE_ITEM: [
        {
            val: 'GENERAL_INFO',
        },
        {
            val: 'MARKERS',
        },
        {
            val: 'ASSOCIATIONS',
        },
        {
            val: 'ASSIGNEES',
            isHiddenCondition: 'isPersonalBase',
        },
    ],
};

export default {
    name: 'DuplicateItemModal',
    components: {

    },
    mixins: [
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
        contextItemType: {
            type: String,
            required: true,
        },
        duplicateItemMethod: {
            type: Function,
            required: true,
        },
    },
    emits: [
        'closeModal',
    ],
    data() {
        return {
            processing: false,
            duplicationAspects: ['GENERAL_INFO'],
        };
    },
    computed: {
        modalHeader() {
            return this.$t('functionality.duplication.items.header', { name: this.name });
        },
        name() {
            return this.item?.name;
        },
        duplicationOptions() {
            return options[this.contextItemType];
        },
        filteredOptions() {
            return this.duplicationOptions.filter((option) => {
                // If isHiddenCondition exists, display option only if its value is false
                if (option.isHiddenCondition) {
                    return !this[option.isHiddenCondition];
                }
                return true;
            });
        },
        isPersonalBase() {
            return !isActiveBaseCollaborative();
        },
    },
    methods: {
        closeModal() {
            this.$emit('closeModal');
        },
        async duplicateItem() {
            this.processing = true;
            const records = {};
            this.duplicationOptions.forEach((option) => {
                if (!['FEATURES', 'GENERAL_INFO'].includes(option.val)) {
                    const key = `with${_.pascalCase(option.val)}`;

                    records[key] = this.duplicationAspects.includes(option.val);
                }
                if (option.subOptions) {
                    option.subOptions.forEach((subOption) => {
                        const subKey = `with${_.pascalCase(option.val) }${ _.pascalCase(subOption.val)}`;

                        records[subKey] = this.duplicationAspects.includes(subOption.val);
                    });
                }
            });
            try {
                await this.duplicateItemMethod(records);
                this.closeModal();
            } finally {
                this.processing = false;
            }
        },
        getLabel(val) {
            return this.$t(`labels.${_.camelCase(val)}`);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-duplicate-item-modal {

} */

</style>
