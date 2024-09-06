<template>
    <div class="c-dropdown-free">
        <DropdownBasic
            :modelValue="modelValue"
            :optionsPopupProps="optionsPopupProps"
            :hasCircleForSelected="hasCircleForSelected"
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
                    :selectedEvents="selectedEvents"
                    :closePopup="closePopup"
                    :popupState="popupState"
                >
                </slot>
            </template>
            <template
                #option="scope"
            >
                <slot
                    name="wholeOption"
                    v-bind="scope"
                >
                    <DropdownOptions
                        :size="size"
                        :hasCircleForSelected="hasCircleForSelected"
                        v-bind="scope"
                    >
                        <template
                            #option
                        >
                            <slot
                                name="option"
                                v-bind="scope"
                            >
                            </slot>
                        </template>
                    </DropdownOptions>
                </slot>
            </template>

            <template
                v-for="(_, slot) of proxySlots()"
                #[slot]="scope"
            >
                <slot
                    :name="slot"
                    v-bind="scope"
                />
            </template>
        </DropdownBasic>
    </div>
</template>

<script>

import interactsWithDropdowns from '@/vue-mixins/interactsWithDropdowns.js';

export default {
    name: 'DropdownFree',
    components: {
    },
    mixins: [
        interactsWithDropdowns,
    ],
    props: {
        hasCircleForSelected: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
    },
    methods: {
        // proxySlots needs to be a method because $slots is not reactive (See DropdownBox for more info).
        proxySlots() {
            return _.omit(this.$slots, ['selected', 'option']);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-dropdown-free {

} */

</style>
