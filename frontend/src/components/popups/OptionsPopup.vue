<!-- This is used for options that trigger an action -->

<template>
    <PopupBasic
        ref="options"
        class="c-options-popup"
        :class="popupLocationClass"
        :activator="activator"
        v-bind="$attrs"
    >
        <ButtonEl
            v-for="(option, index) in optionsMerged"
            :key="option.val"
            class="c-options-popup__option"
            :class="optionClasses(option, index)"
            @click="selectOption(option)"
        >
            <slot
                :option="option"
            >
                <span>
                    <i
                        v-if="option.icon"
                        class="fal fa-fw mr-2"
                        :class="[option.icon, option.iconColor]"
                    >
                    </i>

                    <span
                        v-if="displayAsIs"
                    >
                        {{ option }}
                    </span>

                    <span
                        v-else
                        v-t="namePath(option)"
                    >
                    </span>
                </span>
            </slot>

            <i
                v-if="option.val === selectedVal"
                class="far fa-check text-cm-400 text-xs ml-3"
            >
            </i>
        </ButtonEl>
    </PopupBasic>
</template>

<script>

import hasDropdownAwareArrowControls from '@/vue-mixins/hasDropdownAwareArrowControls.js';
import providesColors from '@/vue-mixins/style/providesColors.js';

const commonOptions = [
    {
        val: 'RENAME',
        icon: 'fa-pen-to-square',
    },
    {
        val: 'DUPLICATE',
        icon: 'fa-copy',
        namePath: 'common.createCopy',
    },
    {
        val: 'MAKE_ENTITY_PAGE',
        icon: 'fa-memo',
        namePath: 'records.options.makeEntityPage',
    },
    {
        val: 'DISSOCIATE_RECORD',
        icon: 'fa-file-dashed-line',
        namePath: 'records.options.dissociateRecord',
    },
    {
        val: 'DELETE',
        icon: 'fa-trash-can',
        borderAbove: true,
        color: 'peach',
    },
];

export default {
    name: 'OptionsPopup',
    components: {

    },
    mixins: [
        providesColors,
        hasDropdownAwareArrowControls,
    ],
    props: {
        activator: {
            type: HTMLElement,
            required: true,
        },
        options: {
            type: Array,
            required: true,
        },
        popupLocationClass: {
            type: String,
            default: '',
        },
        selectedVal: {
            type: [String, Number, null],
            default: () => (null),
        },
        displayAsIs: Boolean,
    },
    emits: [
        'selectOption',
    ],
    data() {
        return {

        };
    },
    computed: {
        optionsMerged() {
            return this.options.map((option) => {
                const commonOption = _.find(commonOptions, { val: option });
                return commonOption || option;
            });
        },
        optionsLength() {
            return this.optionsMerged.length;
        },
        arrowKeysActive() {
            return true;
        },
    },
    methods: {
        selectOption(option) {
            this.$emit('selectOption', option.val);
        },
        namePath(option) {
            return option.namePath || `common.${_.camelCase(option.val)}`;
        },
        optionClasses(option, index) {
            const classes = [];
            if (option.color) {
                classes.push(this.getTextColor(option.color));
            }
            if (option.borderAbove) {
                classes.push('border-t border-cm-100 border-solid');
            }
            if (index === this.hoveredIndex) {
                classes.push('bg-cm-100');
            }
            return classes.join(' ');
        },
        onSelectOption(index) {
            this.selectOption(this.optionsMerged[index]);
        },
        arrowKeyElement() {
            return this.activator;
        },
    },
    created() {

    },
};
</script>

<style>

.c-options-popup {
    @apply
        text-cm-600
        text-xssm
    ;

    &__option {
        @apply
            flex
            items-center
            justify-between
            px-4
            py-2
            w-full
        ;

        &:hover {
            @apply
                bg-cm-100
            ;
        }
    }
}

</style>
