<template>
    <div
        class="c-tile1 tile-style flex flex-col p-3"
        :class="mainClass"
    >
        <div
            class="flex items-start flex-1"
            :class="{ 'mb-4': hasBodyContent }"
        >
            <div
                v-if="!hideSlot('IMAGE1')"
                class="mr-4"
            >
                <SlotItem
                    sizeInstructions="h-8 w-8"
                    :selectorMode="selectorMode"
                    :dataObj="dataMap.IMAGE1"
                    :blank="blank"
                    blankClasses="cardDesigns__circle"
                    :isSelected="isSelectedSlot('IMAGE1')"
                    v-bind="slotItemProps"
                    @selectSlot="selectSlot($event, 'IMAGE1')"
                >
                </SlotItem>
            </div>

            <SlotItem
                class="mr-2 flex-1 min-w-0"
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

        <div
            v-if="!hideSlot('REG1')"
            class="mb-2"
        >
            <SlotItem
                :selectorMode="selectorMode"
                :dataObj="dataMap.REG1"
                :blank="blank"
                :isSelected="isSelectedSlot('REG1')"
                v-bind="slotItemProps"
                @selectSlot="selectSlot($event, 'REG1')"
            >
            </SlotItem>
        </div>

        <SlotItem
            v-if="!hideSlot('REG2')"
            :selectorMode="selectorMode"
            :dataObj="dataMap.REG2"
            :blank="blank"
            :isSelected="isSelectedSlot('REG2')"
            v-bind="slotItemProps"
            @selectSlot="selectSlot($event, 'REG2')"
        >
        </SlotItem>
    </div>
</template>

<script>

import interactsWithCardDesigns from '@/vue-mixins/style/interactsWithCardDesigns.js';

export default {
    name: 'Tile1',
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
        hasBodyContent() {
            return !this.hideSlot('REG2') || !this.hideSlot('REG1');
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-tile1 {

} */

</style>
