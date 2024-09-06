<template>
    <div
        class="c-line1 line-style p-3 flex items-center"
        :class="mainClass"
    >
        <div
            v-if="!hideSlot('IMAGE1')"
            class="mr-4"
        >
            <SlotItem
                :dataObj="dataMap.IMAGE1"
                blankClasses="cardDesigns__circle"
                sizeInstructions="h-8 w-8"
                :isSelected="isSelectedSlot('IMAGE1')"
                v-bind="slotItemProps"
                @selectSlot="selectSlot($event, 'IMAGE1')"
            >
            </SlotItem>
        </div>

        <div class="c-line1__grid">
            <SlotItem
                :dataObj="dataMap.HEADER1"
                blankClasses="cardDesigns__blank cardDesigns__blank--header"
                :isSelected="isSelectedSlot('HEADER1')"
                v-bind="slotItemProps"
                @selectSlot="selectSlot($event, 'HEADER1')"
            >
            </SlotItem>

            <div class="c-line1__inner">
                <SlotItem
                    :class="{ 'w-full': blank || (selectorMode && !dataMap.REG1) }"
                    :dataObj="dataMap.REG1"
                    :isSelected="isSelectedSlot('REG1')"
                    v-bind="slotItemProps"
                    @selectSlot="selectSlot($event, 'REG1')"
                >
                </SlotItem>

                <SlotItem
                    :class="{ 'w-full': blank || (selectorMode && !dataMap.REG2) }"
                    :dataObj="dataMap.REG2"
                    containerPropClass="items-end"
                    :isSelected="isSelectedSlot('REG2')"
                    v-bind="slotItemProps"
                    @selectSlot="selectSlot($event, 'REG2')"
                >
                </SlotItem>
            </div>
        </div>

        <EntityExtras
            v-if="isModifiable"
            v-bind="extraProps"
            :duplicateItemMethod="duplicateRecord"
            @selectOption="selectOption"
        >
        </EntityExtras>
    </div>
</template>

<script>

import interactsWithCardDesigns from '@/vue-mixins/style/interactsWithCardDesigns.js';

export default {
    name: 'Line1',
    components: {

    },
    mixins: [
        interactsWithCardDesigns,
    ],
    props: {

    },
    data() {
    },
    computed: {

    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-line1 {
    &__grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));

        @apply
            flex-1
            gap-2
            grid
            items-center
            mr-4
        ;
    }

    &__inner {
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));

        @apply
            gap-2
            grid
            items-center
        ;
    }
}

</style>
