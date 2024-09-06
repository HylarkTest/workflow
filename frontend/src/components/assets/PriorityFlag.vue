<template>
    <div
        v-blur="closePopup"
        class="c-priority-flag relative"
    >
        <component
            ref="button"
            :is="isModifiable ? 'button' : 'div'"
            class="c-priority-flag__flag"
            :class="colorCall"
            :type="isModifiable ? 'button' : ''"
            @click="togglePopup"
        >
            <slot
                :priority="priority"
                :colorCall="colorCall"
                :priorityColor="priorityColor"
                :colorIntensity="colorIntensity"
            >
                <i
                    class="fa-flag"
                    :class="priority ? 'fas' : 'far'"
                >
                </i>
            </slot>
        </component>

        <!-- <ClearButton
            v-if="priority && !hideClear && isModifiable"
            class="c-priority-flag__remove circle-center transition-2eio"
            @click="selectPriority(0)"
        >
        </ClearButton> -->

        <OptionsPopup
            v-if="popupVisible"
            :activator="$refs.button"
            :options="options"
            :selectedVal="priority"
            alignRight
            nudgeDownProp="0.375rem"
            nudgeRightProp="0.625rem"
            @selectOption="selectPriority"
        >
        </OptionsPopup>
    </div>
</template>

<script>

import OptionsPopup from '@/components/popups/OptionsPopup.vue';
// import ClearButton from '@/components/buttons/ClearButton.vue';

import providesColors from '@/vue-mixins/style/providesColors.js';

export default {
    name: 'PriorityFlag',
    components: {
        OptionsPopup,
        // ClearButton,
    },
    mixins: [
        providesColors,
    ],
    props: {
        priority: {
            type: Number,
            required: true,
        },
        allowedPriorities: {
            type: Array,
            default: null,
        },
        hideClear: Boolean,
        isModifiable: Boolean,
    },
    emits: [
        'selectPriority',
    ],
    data() {
        return {
            popupVisible: false,
        };
    },
    computed: {
        priorityColor() {
            return this.getPriorityColor(this.priority);
        },
        colorIntensity() {
            if (this.priority === 0) {
                return '300';
            }
            return this.priorityColor === 'gray' ? '400' : '600';
        },
        colorCall() {
            return this.getTextColor(this.priorityColor, this.colorIntensity);
        },
        optionsList() {
            const options = [
                {
                    val: 0,
                    icon: 'far fa-times',
                    namePath: 'common.clear',
                    iconColor: this.getColorClass(this.getPriorityColor(0)),
                    exclude: !this.priority,
                },
                {
                    val: 1,
                    icon: 'fas fa-flag',
                    namePath: 'common.priorities.urgent',
                    borderAbove: true,
                    iconColor: this.getColorClass(this.getPriorityColor(1)),
                },
                {
                    val: 3,
                    icon: 'fas fa-flag',
                    namePath: 'common.priorities.high',
                    iconColor: this.getColorClass(this.getPriorityColor(3)),
                },
                {
                    val: 5,
                    icon: 'fas fa-flag',
                    namePath: 'common.priorities.normal',
                    iconColor: this.getColorClass(this.getPriorityColor(5)),
                },
                {
                    val: 9,
                    icon: 'fas fa-flag',
                    namePath: 'common.priorities.low',
                    iconColor: this.getColorClass(this.getPriorityColor(9)),
                },
            ];

            if (this.allowedPriorities) {
                return options.filter((option) => this.allowedPriorities.includes(option.val));
            }
            return options;
        },
        options() {
            return this.optionsList.filter((option) => {
                return !option.exclude;
            });
        },
    },
    methods: {
        closePopup() {
            this.popupVisible = false;
        },
        togglePopup() {
            if (this.isModifiable) {
                this.popupVisible = !this.popupVisible;
            }
        },
        getPriorityColor(priority) {
            switch (priority) {
            case 1:
            case 2:
                return 'red';
            case 3:
            case 4:
                return 'gold';
            case 5:
            case 6:
                return 'sky';
            default:
                return 'gray';
            }
        },
        getColorClass(priority) {
            const intensity = priority === 'gray' ? '400' : '600';
            return this.getTextColor(priority, intensity);
        },
        selectPriority(priority) {
            this.closePopup();
            this.$emit('selectPriority', priority);
        },
    },
    created() {
    },
};
</script>

<style scoped>

.c-priority-flag {
    @apply
        inline-flex
    ;

    &__flag:hover + .c-priority-flag__remove {
        @apply
            opacity-100
        ;
    }

    &__remove {
        @apply
            opacity-0
        ;

        &:hover {
            @apply
                opacity-100
            ;
        }
    }
}

</style>
