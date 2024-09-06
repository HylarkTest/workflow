<template>
    <button
        class="c-save-button relative"
        :class="customClasses"
        :type="buttonType"
        :disabled="disabled"
        :title="title"
        @click="$emit('save')"
    >
        <slot>
            <span
                v-t="textPath"
            >
            </span>
        </slot>
        <span
            v-if="disabled"
            class="h-full w-full bg-white absolute top-0 right-0 opacity-50 pointer-events-none block"
        >
            &nbsp;
        </span>
        <transition name="t-fade">
            <AlertTooltip
                v-if="errorMessage"
            >
                {{ errorMessage }}
            </AlertTooltip>
        </transition>
    </button>
</template>

<script>

import AlertTooltip from '@/components/popups/AlertTooltip.vue';

export default {
    name: 'SaveButton',
    components: {
        AlertTooltip,
    },
    props: {
        // Shows in the button
        textPath: {
            type: String,
            default: 'common.save',
        },
        buttonClass: {
            type: String,
            default: 'button--lg',
            validator(val) {
                return ['button--lg', 'button', 'button--sm'].includes(val);
            },
        },
        colorClass: {
            type: String,
            default: 'button-primary',
            validator(val) {
                return ['button-primary', 'button-secondary', 'button-primary--border'].includes(val);
            },
        },
        disabled: Boolean,
        pulse: Boolean,
        error: {
            type: String,
            default: '',
        },
        // Shows in the title attribute
        titlePath: {
            type: String,
            default: 'common.save',
        },
        titleString: {
            type: String,
            default: '',
        },
        buttonType: {
            type: String,
            default: 'submit',
        },
    },
    emits: [
        'save',
    ],
    computed: {
        errorMessage() {
            // Written out here to make it expandable to add more things
            return this.error;
        },
        customClasses() {
            return `${this.buttonClass} ${this.colorClass} ${this.effectClass}`;
        },
        effectClass() {
            if (this.disabled) {
                return 'pointer-events-none';
            }
            let classes = 'pointer-events-auto ';
            if (this.pulse) {
                classes = classes.concat('c-save-button--pulse');
            }
            return classes;
        },
        title() {
            return this.titleString || this.$t(this.titlePath);
        },
    },
};
</script>

<style scoped>

.c-save-button {
    &--pulse {
        animation-duration: 0.4s;
        animation-iteration-count: 2;
        animation-name:  pulse;
    }
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

</style>
