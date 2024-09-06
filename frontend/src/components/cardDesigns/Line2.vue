<template>
    <div
        class="c-line2 line-style p-3 flex items-center"
        :class="mainClass"
    >
        <div class="mr-4">
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

        <div class="c-line2__main">
            <div
                class="c-line2__header"
            >
                <SlotItem
                    :dataObj="dataMap.HEADER1"
                    blankClasses="cardDesigns__blank cardDesigns__blank--header"
                    :isSelected="isSelectedSlot('HEADER1')"
                    v-bind="slotItemProps"
                    @selectSlot="selectSlot($event, 'HEADER1')"
                >
                </SlotItem>
            </div>

            <div
                class="c-line2__content flex-1"
            >
                <SlotItem
                    class="c-line2__content flex-1"
                    :dataObj="dataMap.REG1"
                    :isSelected="isSelectedSlot('REG1')"
                    v-bind="slotItemProps"
                    @selectSlot="selectSlot($event, 'REG1')"
                >
                </SlotItem>
            </div>

            <div
                class="c-line2__content flex-1"
            >
                <SlotItem
                    :class="{ 'w-full': blank || (selectorMode && !dataMap.REG2) }"
                    :dataObj="dataMap.REG2"
                    :isSelected="isSelectedSlot('REG2')"
                    v-bind="slotItemProps"
                    @selectSlot="selectSlot($event, 'REG2')"
                >
                </SlotItem>
            </div>

            <div
                class="c-line2__content flex-1"
            >
                <SlotItem
                    :class="{ 'w-full': blank || (selectorMode && !dataMap.REG3) }"
                    :dataObj="dataMap.REG3"
                    containerPropClass="items-end"
                    :isSelected="isSelectedSlot('REG3')"
                    v-bind="slotItemProps"
                    @selectSlot="selectSlot($event, 'REG3')"
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
    name: 'Line2',
    components: {

    },
    mixins: [
        interactsWithCardDesigns,
    ],
    props: {

    },
    data() {
        return {
        };
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

.c-line2 {
    &__main {
        @apply
            flex
            flex-1
            flex-wrap
            gap-2
            items-center
            min-w-0
            mr-4
        ;
    }

    &__header {
        flex-grow: 2;
        min-width: 150px;

        @apply
            basis-0
            shrink
        ;
    }

    &__content {
        min-width: 120px;
    }
}

</style>
