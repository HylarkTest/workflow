<template>
    <div class="c-slot-item relative">
        <component
            v-if="showGraySlot"
            :is="isClickableGray ? 'ButtonEl' : 'div'"
            :class="[blankClasses, selectedClass, sizeInstructions]"
            :unlickable="!isClickableGray"
            @click="selectSlot"
        >
        </component>
        <DisplayerContainer
            v-if="hasDataObj"
            :dataInfo="dataObj.dataInfo"
            :item="dataObj.item"
            :class="componentClasses"
            :sizeInstructions="sizeInstructions"
            v-bind="$attrs"
            @click="selectSlot"
        >
        </DisplayerContainer>

        <HighlightBar
            v-if="isClickable && hasDataObj"
            :class="isSelected ? 'opacity-100' : 'opacity-0'"
        />
    </div>
</template>

<script>

export default {
    name: 'SlotItem',
    components: {

    },
    mixins: [
    ],
    props: {
        selectorMode: Boolean,
        dataObj: {
            type: [Object, null],
            default: null,
        },
        blank: Boolean,
        previewMode: Boolean,
        isSelected: Boolean,
        blankClasses: {
            type: String,
            default: 'cardDesigns__blank',
        },
        sizeInstructions: {
            type: String,
            default: '',
        },
    },
    emits: [
        'selectSlot',
    ],
    data() {
        return {

        };
    },
    computed: {
        showGraySlot() {
            return this.blank || (!this.hasDataObj && this.selectorMode);
        },
        hasDataObj() {
            return !_.isEmpty(this.dataObj);
        },
        componentClasses() {
            return [this.clickableClass];
        },
        isClickableGray() {
            return this.selectorMode;
        },
        isClickable() {
            return this.selectorMode || this.previewMode;
        },
        clickableClass() {
            return { 'cursor-pointer': this.isClickable };
        },
        selectedClass() {
            return { 'bg-primary-100 shadow-lg shadow-primary-600/20': this.isSelected };
        },
    },
    methods: {
        selectSlot() {
            if (this.isClickable) {
                this.$emit('selectSlot', {
                    dataInfo: this.dataObj?.dataInfo,
                });
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-slot-item {

} */

</style>
