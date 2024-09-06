<template>
    <div
        class="c-kanban2 kanban-style"
        :class="mainClass"
    >

        <div class="p-3">
            <div class="mb-4 flex items-baseline">
                <div
                    v-if="!hideSlot('REG1')"
                    class="flex-1"
                >
                    <SlotItem
                        v-if="!hideSlot('REG1')"
                        :selectorMode="selectorMode"
                        :dataObj="dataMap.REG1"
                        :blank="blank"
                        :isSelected="isSelectedSlot('REG1')"
                        v-bind="slotItemProps"
                        @selectSlot="selectSlot($event, 'REG1')"
                    >
                    </SlotItem>
                </div>

                <EntityExtras
                    v-if="isModifiable"
                    v-bind="extraProps"
                    :duplicateItemMethod="duplicateRecord"
                    @selectOption="selectOption"
                >
                </EntityExtras>
            </div>

            <SlotItem
                class="mb-2"
                :dataObj="dataMap.HEADER1"
                blankClasses="cardDesigns__blank cardDesigns__blank--header"
                :isSelected="isSelectedSlot('HEADER1')"
                v-bind="slotItemProps"
                @selectSlot="selectSlot($event, 'HEADER1')"
            >
            </SlotItem>

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

        <div
            v-if="hasBodyContent"
            class="flex gap-2 justify-between items-end p-3 border-t border-solid border-cm-300"
        >
            <div
                v-if="!hideSlot('REG3')"
                class="flex-1"
            >
                <SlotItem
                    :dataObj="dataMap.REG3"
                    blankClasses="cardDesigns__blank"
                    :isSelected="isSelectedSlot('REG3')"
                    v-bind="slotItemProps"
                    @selectSlot="selectSlot($event, 'REG3')"
                >
                </SlotItem>
            </div>

            <SlotItem
                v-if="!hideSlot('REG4')"
                class="flex-1 items-end"
                :dataObj="dataMap.REG4"
                blankClasses="cardDesigns__blank"
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
    name: 'Kanban2',
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
        hasBodyContent() {
            return !this.hideSlot('REG3') || !this.hideSlot('REG4');
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-kanban2 {

} */

</style>
