<template>
    <div class="c-dropdown-line">
        <DropdownLabel
            v-if="inlineLabel && inlineLabel.position === 'outside'"
            :label="inlineLabel"
        >
        </DropdownLabel>

        <DropdownBasic
            class="flex-1"
            :modelValue="modelValue"
            :optionsPopupProps="optionsPopupProps"
            v-bind="$attrs"
            @input="$emit('input', $event)"
        >
            <template
                #selected="scope"
            >
                <DropdownDisplay
                    class="c-dropdown-line__display"
                    :inlineLabel="inlineLabel"
                    v-bind="{
                        ...scope,
                        ...$attrs,
                    }"
                >
                    <template
                        #selected
                    >
                        <slot
                            name="selected"
                            v-bind="scope"
                        >
                        </slot>
                    </template>

                    <template
                        #inlineDisplayAfter
                    >
                        <slot
                            name="inlineDisplayAfter"
                            :original="scope.original"
                            :closePopup="scope.closePopup"
                        >
                        </slot>
                    </template>
                </DropdownDisplay>
            </template>
            <template
                #option="scope"
            >
                <DropdownOptions
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
            </template>

            <template
                #popupEnd
            >
                <slot
                    name="popupEnd"
                >
                </slot>
            </template>
        </DropdownBasic>
    </div>
</template>

<script>

import interactsWithDropdowns from '@/vue-mixins/interactsWithDropdowns.js';

export default {
    components: {
    },
    mixins: [
        interactsWithDropdowns,
    ],
    emits: [
        'input',
    ],
    data() {
        return {
        };
    },
    computed: {

    },
    methods: {
    },
};

</script>

<style scoped>

.c-dropdown-line {
    @apply
        inline-flex
        items-baseline
    ;

    &__display {
        @apply
            border-b
            border-solid
            pl-1
        ;
    }
}

</style>
