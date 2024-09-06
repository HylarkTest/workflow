<template>
    <div
        class="o-tip-tap-heading"
        :class="{ unclickable: isGroupDeactivated }"
    >
        <DropdownFree
            :modelValue="activeOption"
            :options="options"
            displayRule="text"
            @update:modelValue="selectOption"
        >
            <template
                #selected="{
                    selectedEvents,
                }"
            >
                <button
                    ref="button"
                    class="o-tip-tap-heading__button"
                    type="button"
                    @click.stop="selectedEvents.click"
                >
                    <p class="flex-grow">
                        {{ activeOption?.text }}
                    </p>
                    <i class="fal fa-ellipsis-v"></i>
                </button>
            </template>
        </DropdownFree>
    </div>
</template>

<script>
import useTipTapEditorOptions from '@/composables/useTipTapEditorOptions.js';

export default {
    name: 'TipTapHeading',
    props: {
        editor: {
            type: Object,
            required: true,
        },
        isGroupDeactivated: Boolean,
    },
    setup(props) {
        const {
            getOptionsGroup,
        } = useTipTapEditorOptions(props);

        return {
            getOptionsGroup,
        };
    },
    computed: {
        options() {
            return this.getOptionsGroup('HEADING');
        },
        activeOption() {
            return _.find(this.options, (option) => option.isActive);
        },
    },
    methods: {
        selectOption(selectedOption) {
            const option = _.find(this.options, { key: selectedOption.key });
            option.action();
        },
    },
};
</script>

<style scoped>
.o-tip-tap-heading {
    &__button {
        @apply
            bg-primary-200
            border
            flex
            h-7
            items-center
            justify-center
            px-2
            py-1
            rounded-md
            w-28
        ;

        &:hover {
            @apply
                bg-primary-300
                border-cm-300
                border-solid
                cursor-pointer
            ;
        }
    }
}
</style>
