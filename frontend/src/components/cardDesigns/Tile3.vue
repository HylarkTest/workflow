<template>
    <div
        class="c-tile3 tile-style flex flex-col p-3"
        :class="mainClass"
    >
        <SlotItem
            v-if="!hideSlot('IMAGE1')"
            class="mb-2"
            blankClasses="cardDesigns__blank w-full"
            sizeInstructions="h-28"
            :dataObj="dataMap.IMAGE1"
            :isSelected="isSelectedSlot('IMAGE1')"
            v-bind="slotItemProps"
            @selectSlot="selectSlot($event, 'IMAGE1')"
        >
        </SlotItem>

        <div class="mb-4 flex items-start flex-1">
            <SlotItem
                class="mr-2 flex-1"
                :selectorMode="selectorMode"
                :dataObj="dataMap.HEADER1"
                :blank="blank"
                blankClasses="cardDesigns__blank cardDesigns__blank--header"
                :isSelected="isSelectedSlot('HEADER1')"
                v-bind="slotItemProps"
                @selectSlot="selectSlot($event, 'HEADER1')"
            >
            </SlotItem>

            <EntityExtras
                v-if="isModifiable"
                v-bind="extraProps"
                :duplicateItemMethod="duplicateRecord"
                @selectOption="selectOption"
            >
            </EntityExtras>
        </div>

        <SlotItem
            v-if="!hideSlot('REG1')"
            class="mb-2"
            :selectorMode="selectorMode"
            :dataObj="dataMap.REG1"
            :blank="blank"
            :isSelected="isSelectedSlot('REG1')"
            v-bind="slotItemProps"
            @selectSlot="selectSlot($event, 'REG1')"
        >
        </SlotItem>

        <SlotItem
            v-if="!hideSlot('REG2')"
            class="mb-2"
            :selectorMode="selectorMode"
            :dataObj="dataMap.REG2"
            :blank="blank"
            :isSelected="isSelectedSlot('REG2')"
            v-bind="slotItemProps"
            @selectSlot="selectSlot($event, 'REG2')"
        >
        </SlotItem>

        <div class="flex items-end justify-between gap-2">
            <SlotItem
                class="flex-1 min-w-0"
                :selectorMode="selectorMode"
                :dataObj="dataMap.REG3"
                :blank="blank"
                :isSelected="isSelectedSlot('REG3')"
                v-bind="slotItemProps"
                @selectSlot="selectSlot($event, 'REG3')"
            >
            </SlotItem>

            <SlotItem
                class="flex-1 items-end min-w-0"
                :selectorMode="selectorMode"
                :dataObj="dataMap.REG4"
                :blank="blank"
                :isSelected="isSelectedSlot('REG4')"
                v-bind="slotItemProps"
                @selectSlot="selectSlot($event, 'REG4')"
            >
            </SlotItem>
        </div>
    </div>
</template>

<script>

import interactsWithCardDesigns from '@/vue-mixins/style/interactsWithCardDesigns.js';

export default {
    name: 'Tile3',
    components: {

    },
    mixins: [
        interactsWithCardDesigns,
    ],
    props: {

    },
    emits: [
        'selectSlot',
    ],
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

/*.c-tile3 {

} */

</style>
