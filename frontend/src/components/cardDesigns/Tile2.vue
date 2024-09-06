<template>
    <div
        class="c-tile2 tile-style"
        :class="mainClass"
    >
        <div class="mb-2">
            <SlotItem
                sizeInstructions="h-40 w-full"
                :dataObj="dataMap.IMAGE1"
                blankClasses="cardDesigns__blank w-full"
                :isSelected="isSelectedSlot('IMAGE1')"
                v-bind="slotItemProps"
                @selectSlot="selectSlot($event, 'IMAGE1')"
            >
            </SlotItem>
        </div>

        <div class="p-3 flex flex-col">
            <div class="mb-3 flex justify-between">
                <SlotItem
                    class="mr-2 flex-1 min-w-0"
                    :dataObj="dataMap.HEADER1"
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
                :dataObj="dataMap.REG1"
                :isSelected="isSelectedSlot('REG1')"
                v-bind="slotItemProps"
                @selectSlot="selectSlot($event, 'REG1')"
            >
            </SlotItem>

            <div
                :class="{ 'mt-2': !hideSlot('REG1') }"
            >
                <SlotItem
                    v-if="!hideSlot('REG2')"
                    :dataObj="dataMap.REG2"
                    :isSelected="isSelectedSlot('REG2')"
                    v-bind="slotItemProps"
                    @selectSlot="selectSlot($event, 'REG2')"
                >
                </SlotItem>
            </div>
        </div>
    </div>
</template>

<script>

import interactsWithCardDesigns from '@/vue-mixins/style/interactsWithCardDesigns.js';

export default {
    name: 'Tile2',
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

/*.c-tile2 {

} */

</style>
