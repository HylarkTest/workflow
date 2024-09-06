<template>
    <div class="c-kanban5 kanban-style">

        <div class="p-3">
            <div class="mb-3 flex">
                <SlotItem
                    class="mr-2 flex-1"
                    :dataObj="dataMap.HEADER1"
                    blankClasses="cardDesigns__blank cardDesigns__blank--header"
                    :isSelected="isSelectedSlot('HEADER1')"
                    v-bind="slotItemProps"
                    @selectSlot="selectSlot($event, 'HEADER1')"
                >
                </SlotItem>

                <EntityExtras
                    :page="page"
                    :hideView="!isModifiable"
                    :cantModify="!isModifiable"
                    :mapping="mapping"
                    :item="dataValueObject"
                    :duplicateItemMethod="duplicateRecord"
                >
                </EntityExtras>
            </div>

            <SlotItem
                class="mb-3"
                sizeInstructions="h-16 w-full"
                :dataObj="dataMap.IMAGE1"
                blankClasses="cardDesigns__blank h-full"
                :isSelected="isSelectedSlot('IMAGE1')"
                v-bind="slotItemProps"
                @selectSlot="selectSlot($event, 'IMAGE1')"
            >
            </SlotItem>

            <SlotItem
                class="mb-2"
                :dataObj="dataMap.REG1"
                :isSelected="isSelectedSlot('REG1')"
                v-bind="slotItemProps"
                @selectSlot="selectSlot($event, 'REG1')"
            >
            </SlotItem>

            <SlotItem
                :dataObj="dataMap.REG2"
                :isSelected="isSelectedSlot('REG2')"
                v-bind="slotItemProps"
                @selectSlot="selectSlot($event, 'REG2')"
            >
            </SlotItem>
        </div>

        <div class="p-3 flex">
            <SlotItem
                class="mr-2"
                :dataObj="dataMap.REG3"
                blankClasses="cardDesigns__blank w-16"
                :isSelected="isSelectedSlot('REG3')"
                v-bind="slotItemProps"
                @selectSlot="selectSlot($event, 'REG3')"
            >
            </SlotItem>

            <SlotItem
                :dataObj="dataMap.REG4"
                blankClasses="cardDesigns__blank w-16"
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
    name: 'Kanban5',
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

/*.c-kanban5 {

} */

</style>
