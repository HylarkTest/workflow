<template>
    <div class="o-design-options">

        <div
            v-if="additional && additional.length"
            class="-m-2 flex flex-wrap border-b border-solid mb-6 pb-4 border-cm-200"
        >
            <div
                v-for="option in additional"
                :key="option"
                class="p-2 w-1/2"
            >
                <ButtonEl
                    class="o-design-options__option hover:shadow-lg h-full centered"
                    :class="selectedAdditionalClass(option)"
                    @click="selectAdditional(option)"
                >
                    {{ $t('labels.' + option) }}
                </ButtonEl>
            </div>
        </div>

        <p
            v-if="showWarning"
            class="text-xs mb-2"
        >
            *Certain options may be dependent on browser support.
        </p>

        <div
            class="-m-2 flex flex-wrap"
        >
            <div
                v-for="option in combos"
                :key="option"
                class="p-2 w-1/2"
            >
                <ButtonEl
                    class="o-design-options__option hover:shadow-lg h-full centered flex-col"
                    :class="{
                        'o-design-options__option--selected shadow-lg shadow-primary-600/20': isSelected(option),
                    }"
                    @click="selectCombo(option)"
                >
                    <DisplayerContainer
                        :item="mockData"
                        :page="null"
                        :isSummaryView="true"
                        :dataInfo="{ ...data, combo: option }"
                    >
                    </DisplayerContainer>

                    <p
                        v-if="getLangVal(option)"
                        class="text-center block mt-2 text-xssm font-medium"
                    >
                        {{ valName(option) }}
                    </p>
                </ButtonEl>
            </div>
        </div>
    </div>
</template>

<script>

import {
    getFormattedMockData,
    getDesignInfo,
    getDefaultCombo,
    getDefaultAdditional,
    getTypeKey,
    getCombo,
} from '@/core/display/displayerInstructions.js';

export default {
    name: 'DesignOptions',
    components: {

    },
    mixins: [
    ],
    props: {
        data: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:data',
    ],
    data() {
        return {
        };
    },
    computed: {
        selectedCombo: {
            get() {
                return this.data.combo || getDefaultCombo(this.data);
            },
            set(combo) {
                this.$proxyEvent(combo, this.data, 'combo', 'update:data');
            },
        },
        selectedAdditional: {
            get() {
                return this.data.designAdditional || getDefaultAdditional(this.data);
            },
            set(additional) {
                this.$proxyEvent(additional, this.data, 'designAdditional', 'update:data');
            },
        },
        designInfo() {
            return getDesignInfo(this.data);
        },
        combos() {
            if (this.designInfo?.displayInfo?.combos) {
                return this.designInfo?.displayInfo.combos;
            }
            return this.designInfo?.displayInfo;
        },

        additional() {
            return this.designInfo?.displayInfo?.additional;
        },

        typeKey() {
            return getTypeKey(this.data);
        },
        mockData() {
            return getFormattedMockData(this.data);
        },
        showWarning() {
            return ['IMAGE'].includes(this.typeKey);
        },
    },
    methods: {
        selectCombo(option) {
            this.selectedCombo = option;
        },
        isSelected(option) {
            return this.selectedCombo === option;
        },
        selectAdditional(option) {
            this.selectedAdditional = option;
        },
        selectedAdditionalClass(option) {
            return {
                'o-design-options__option--selected shadow-lg shadow-primary-600/20': this.isSelectedAdditional(option),
            };
        },
        isSelectedAdditional(option) {
            return this.selectedAdditional === option;
        },
        fullComboObj(comboNumber) {
            return getCombo(this.typeKey, comboNumber);
        },
        getLangVal(comboNumber) {
            return this.fullComboObj(comboNumber)?.langVal;
        },
        valName(comboNumber) {
            const val = _.camelCase(this.getLangVal(comboNumber));
            return this.$t(`designElements.${val}`);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-design-options {
    &__option {
        transition: 0.2s ease-in-out;

        @apply
            border
            border-cm-200
            border-solid
            p-4
            rounded-xl
        ;

        &--selected {
            @apply
                border-primary-600
            ;
        }
    }
}

</style>
