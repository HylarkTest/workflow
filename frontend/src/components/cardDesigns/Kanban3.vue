<template>
    <div
        class="c-kanban3 kanban-style"
        :class="mainClass"
    >

        <div class="px-4 py-3">
            <div class="mb-3 flex items-start gap-2">
                <SlotItem
                    v-if="!hideSlot('IMAGE1')"
                    sizeInstructions="h-10 w-10"
                    :selectorMode="selectorMode"
                    :dataObj="dataMap.IMAGE1"
                    :blank="blank"
                    blankClasses="cardDesigns__circle"
                    :isSelected="isSelectedSlot('IMAGE1')"
                    v-bind="slotItemProps"
                    @selectSlot="selectSlot($event, 'IMAGE1')"
                >
                </SlotItem>

                <SlotItem
                    class="flex-1"
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
                class="mt-2"
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
                class="mt-2"
                :selectorMode="selectorMode"
                :dataObj="dataMap.REG2"
                :blank="blank"
                :isSelected="isSelectedSlot('REG2')"
                v-bind="slotItemProps"
                @selectSlot="selectSlot($event, 'REG2')"
            >
            </SlotItem>

            <SlotItem
                v-if="!hideSlot('REG3')"
                class="flex-1 mt-2"
                :selectorMode="selectorMode"
                :dataObj="dataMap.REG3"
                :blank="blank"
                :isSelected="isSelectedSlot('REG3')"
                v-bind="slotItemProps"
                @selectSlot="selectSlot($event, 'REG3')"
            >
            </SlotItem>

            <SlotItem
                v-if="!hideSlot('REG4')"
                class="flex-1 mt-2"
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
    name: 'Kanban3',
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
            return !this.hideSlot('IMAGE1') || !this.hideSlot('REG3');
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-kanban3 {

} */

</style>
