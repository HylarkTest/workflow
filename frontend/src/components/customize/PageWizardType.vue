<template>
    <div class="o-page-wizard-type">
        <div class="max-w-xl">
            <h2 class="o-creation-wizard__prompt mt-4">
                What type of page are you creating?
            </h2>

            <div class="flex flex-wrap justify-center max-w-xl">
                <SnazzyOption
                    v-for="option in fullOptions"
                    :key="option.val"
                    :isSelected="isSelected(option.val)"
                    class="m-2"
                    @click="setOption(option.val)"
                    @keyup.enter="setOption(option.val)"
                    @keyup.space="setOption(option.val)"
                >
                    <DuotoneIconText
                        :name="$t(namePath(option.val))"
                        :icon="option.symbol"
                    >
                    </DuotoneIconText>
                </SnazzyOption>
            </div>
        </div>
    </div>
</template>

<script>

import SnazzyOption from '@/components/buttons/SnazzyOption.vue';
import DuotoneIconText from '@/components/display/DuotoneIconText.vue';

import { featurePages, entityPagesList } from '@/core/display/typenamesList.js';

const options = [
    'ENTITIES',
    'ENTITY',
    'CALENDAR',
    'TODOS',
    'PINBOARD',
    'LINKS',
    'DOCUMENTS',
    'NOTES',
];

export default {
    name: 'PageWizardType',
    components: {
        SnazzyOption,
        DuotoneIconText,
    },
    mixins: [
    ],
    props: {
        pageForm: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:pageForm',
    ],
    data() {
        return {
        };
    },
    computed: {
        combined() {
            return {
                ...featurePages,
                ...entityPagesList,
            };
        },
        fullOptions() {
            return options.map((option) => {
                return this.combined[option];
            });
        },
    },
    methods: {
        isSelected(val) {
            return this.pageForm.type === val;
        },
        setOption(val) {
            if (this.isSelected(val)) {
                this.$emit('update:pageForm', { valKey: 'type', newVal: null });
            } else {
                this.$emit('update:pageForm', { valKey: 'type', newVal: val, nextStep: 'NEXT' });
            }
        },
        namePath(val) {
            return `common.pageTypes.${_.camelCase(val)}`;
        },
    },
    created() {
    },
};
</script>

<style scoped>

/*.o-page-wizard-type {

} */

</style>
