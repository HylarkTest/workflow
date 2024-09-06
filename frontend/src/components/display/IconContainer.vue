<template>
    <div class="c-icon-container">
        <div
            class="mb-4"
        >
            <h4
                class="font-semibold text-2xl text-cm-700"
                :class="headerClass"
            >
                {{ header }}
            </h4>

            <p
                v-if="$slots.subheader"
                class="mt-2 text-cm-600 text-smbase"
            >
                <slot
                    name="subheader"
                >
                </slot>
            </p>
        </div>
        <div class="flex">
            <div
                class="hidden sm:block mr-6 md:w-100p text-3xl md:text-5xl"
                :class="iconClass"
            >

                <QuarterCircle
                    class="h-16 w-16 md:h-24 md:w-24 centered"
                    :style="quarterColor"
                    point="bottom-left"
                >
                    <i
                        class="-ml-3 mt-5 fa-fw"
                        :class="icon"
                    >
                    </i>
                </QuarterCircle>
            </div>
            <div class="flex-1">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script>

import providesColors from '@/vue-mixins/style/providesColors.js';

export default {
    name: 'IconContainer',
    components: {

    },
    mixins: [
        providesColors,
    ],
    props: {
        icon: {
            type: String,
            required: true,
        },
        header: {
            type: String,
            required: true,
        },
        headerClass: {
            type: String,
            default: '',
        },
        iconClass: {
            type: String,
            default: '',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        quarterColor() {
            return {
                backgroundColor: this.complementaryHex(this.accentColor, '100'),
                color: this.complementaryHex(this.accentColor, '600'),
            };
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<!-- <style scoped>
.c-icon-container {
}
</style> -->
