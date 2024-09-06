<template>
    <div
        class="c-icons-composition"
    >
        <div
            v-if="firstIcon"
            class="c-icons-composition__main centered"
            :class="[sizeClass, colorClasses, roundedClass]"
        >
            <i
                class="fal"
                :class="firstIcon.symbol"
            >
            </i>
        </div>
        <div
            v-if="showRemainingIcons && hasRemainingIcons"
            class="absolute top-0 -right-2"
        >
            <div
                v-for="(icon, index) in remainingIcons"
                :key="index"
                class="c-icons-composition__remaining centered"
                :class="remainingColorClasses(icon)"
            >
                <i
                    class="far"
                    :class="icon.symbol"
                >
                </i>
            </div>
        </div>
    </div>
</template>

<script>

import providesColors from '@/vue-mixins/style/providesColors.js';

export default {
    name: 'IconsComposition',
    components: {

    },
    mixins: [
        providesColors,
    ],
    props: {
        iconsComposition: {
            type: Array,
            required: true,
        },
        sizeClass: {
            type: String,
            required: true,
        },
        showRemainingIcons: Boolean,
        roundedClass: {
            type: String,
            default: 'rounded-xl',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        firstIcon() {
            return this.iconsComposition[0];
        },
        remainingIcons() {
            const plus = {
                symbol: 'fa-plus',
                color: 'primary',
            };

            const iconsArr = this.iconsComposition.slice(1, 2);

            if (this.addPlus) {
                iconsArr.push(plus);
            }

            return iconsArr;
        },
        hasRemainingIcons() {
            return this.remainingIcons.length > 0;
        },
        addPlus() {
            return this.iconsComposition.length > 3;
        },
        color() {
            return this.firstIcon.color;
        },
        colorClasses() {
            return `${this.getBgColor(this.color, '100')}
                ${this.getTextColor(this.color)}`;
        },
    },
    methods: {
        remainingColorClasses(icon) {
            return `${this.getBorderColor(icon.color)}
                ${this.getTextColor(icon.color)}`;
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-icons-composition {
    @apply
        relative
    ;

    /*&__main {
        @apply
        ;
    }*/

    &__remaining {
        font-size: 13px;
        height: 20px;
        width:  20px;

        @apply
            bg-cm-00
            border
            border-solid
            mb-1
            rounded-full
        ;
    }
}

</style>
