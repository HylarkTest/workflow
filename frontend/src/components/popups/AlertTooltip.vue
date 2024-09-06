<template>
    <div
        class="c-alert-tooltip"
        :class="[customClass, alertColorClass]"
        :style="alertPosition"
    >
        <div class="c-alert-tooltip__inner">
            <slot></slot>
            <!-- <span
                class="c-alert-tooltip__triangle"
                :class="triangleClass"
                :style="trianglePosition"
            ></span> -->
        </div>
    </div>
</template>

<script>
export default {
    name: 'AlertTooltip',
    props: {
        customClass: {
            type: String,
            default: '',
        },
        // triangleClass: {
        //     type: String,
        //     default: 'c-alert-tooltip__triangle--bottom',
        // },
        // trianglePosition: {
        //     type: Object,
        //     default: () => ({ right: '10px' }),
        // },
        alertPosition: {
            type: Object,
            default: () => ({ bottom: '40px', right: 0 }),
        },
        alertColor: {
            type: String,
            default: 'peach',
            validator: (val) => ['peach', 'gold'].includes(val),

        },
    },
    computed: {
        alertColorClass() {
            return `bg-${this.alertColor}-100 text-${this.alertColor}-800`;
        },
    },
};

</script>

<style scoped>

.c-alert-tooltip {
    @apply
        absolute
        border-none
        m-0
        px-3
        py-1
        rounded-md
        shadow-md
        text-center
        text-sm
        z-alert
    ;

    &__inner {
        @apply relative;
    }
}

.c-alert-tooltip--wider {
    @apply
        absolute
        w-24
    ;
}
</style>
