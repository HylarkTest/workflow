<template>
    <div
        class="c-line3 line-style p-3 flex items-start"
        :class="mainClass"
    >
        <div
            v-if="!hideSlot('IMAGE1')"
            class="mr-4"
        >
            <SlotItem
                :dataObj="dataMap.IMAGE1"
                blankClasses="cardDesigns__circle"
                :isSelected="isSelectedSlot('IMAGE1')"
                sizeInstructions="h-16 w-16"
                v-bind="slotItemProps"
                @selectSlot="selectSlot($event, 'IMAGE1')"
            >
            </SlotItem>
        </div>

        <div class="flex-1">
            <div class="c-line3__main">
                <div
                    class="c-line3__header"
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

                <div class="c-line3__content">
                    <SlotItem
                        :dataObj="dataMap.REG1"
                        :isSelected="isSelectedSlot('REG1')"
                        v-bind="slotItemProps"
                        @selectSlot="selectSlot($event, 'REG1')"
                    >
                    </SlotItem>
                </div>

                <div class="c-line3__content">
                    <SlotItem
                        :class="{ 'w-full': blank || (selectorMode && !dataMap.REG2) }"
                        :dataObj="dataMap.REG2"
                        :isSelected="isSelectedSlot('REG2')"
                        containerPropClass="items-end"
                        v-bind="slotItemProps"
                        @selectSlot="selectSlot($event, 'REG2')"
                    >
                    </SlotItem>
                </div>
            </div>

            <div
                class="c-line3__main mt-3"
            >
                <div class="c-line3__header">
                    <SlotItem
                        :dataObj="dataMap.REG3"
                        :isSelected="isSelectedSlot('REG3')"
                        v-bind="slotItemProps"
                        @selectSlot="selectSlot($event, 'REG3')"
                    >
                    </SlotItem>
                </div>

                <div class="c-line3__content">
                    <SlotItem
                        :dataObj="dataMap.REG4"
                        :isSelected="isSelectedSlot('REG4')"
                        v-bind="slotItemProps"
                        @selectSlot="selectSlot($event, 'REG4')"
                    >
                    </SlotItem>
                </div>

                <div class="c-line3__content">
                    <SlotItem
                        :class="{ 'w-full': blank || (selectorMode && !dataMap.REG5) }"
                        :dataObj="dataMap.REG5"
                        containerPropClass="items-end"
                        :isSelected="isSelectedSlot('REG5')"
                        v-bind="slotItemProps"
                        @selectSlot="selectSlot($event, 'REG5')"
                    >
                    </SlotItem>
                </div>
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
    name: 'Line3',
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

.c-line3 {
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

        @apply
            flex-1
        ;
    }
}

</style>
