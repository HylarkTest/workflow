<template>
    <DropdownFree
        v-model:searchQuery="searchQuery"
        class="c-new-and-picker"
        isSearchable
        :optionsPopupProps="{ widthProp: '12.5rem' }"
        addTypedValue
        v-bind="$attrs"
    >
        <template
            #selected="{
                display, popupState, selectedEvents, original, closePopup,
            }"
        >
            <slot
                name="selected"
                :original="original"
                :display="display"
                :popupState="popupState"
                :closePopup="closePopup"
                :selectedEvents="selectedEvents"
            >
            </slot>
        </template>

        <template
            #noOptions
        >
            <p
                v-if="searchQuery"
                class="text-center text-cm-600 text-xs p-2"
            >
                Press ENTER to create a new section
            </p>
        </template>
    </DropdownFree>
</template>

<script>

export default {
    name: 'NewAndPicker',
    components: {
    },
    mixins: [
    ],
    props: {
    },
    emits: [
    ],
    data() {
        return {
            searchQuery: '',
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

/*.c-new-and-picker {

} */

</style>
