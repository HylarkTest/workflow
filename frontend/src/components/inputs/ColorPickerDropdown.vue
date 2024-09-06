<template>
    <PopupBasic
        class="c-color-picker-dropdown"
        containerClass="py-4"
        :activator="activator"
        widthProp="13.75rem"
    >

        <div
            v-t="titlePath"
            class="text-sm font-semibold mb-2 text-center"
        >
        </div>

        <ColorSwatches
            v-bind="$attrs"
        >
        </ColorSwatches>

        <!-- <ColorPicker2
            v-bind="$attrs"
        >
        </ColorPicker2> -->
    </PopupBasic>
</template>

<script>

// import ColorPicker2 from '@/components/inputs/ColorPicker.vue';
import ColorSwatches from '@/components/inputs/ColorSwatches.vue';

export default {
    name: 'ColorPickerDropdown',
    components: {
        // ColorPicker2,
        ColorSwatches,
    },
    mixins: [
    ],
    props: {
        activator: {
            type: Object,
            required: true,
        },
        titlePath: {
            type: String,
            default: 'common.selections.color',
        },
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

/*.c-color-picker-dropdown {
}*/

</style>
