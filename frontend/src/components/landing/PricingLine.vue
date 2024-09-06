<template>
    <div class="o-pricing-line">
        <div class="o-pricing-line__name">
            <p class="font-semibold">
                <slot
                    name="description"
                >
                </slot>
            </p>
            <div class="flex items-center">
                <slot
                    name="tag"
                >
                </slot>
                <div
                    v-if="showInfo"
                    class="relative ml-2"
                    @mouseover="popupOn = true"
                    @mouseleave="popupOn = false"
                >
                    <div
                        ref="icon"
                        class="o-pricing-line__info"
                    >
                        <i class="fal fa-info-circle">

                        </i>
                    </div>
                    <PopupBasic
                        v-if="popupOn"
                        containerClass="o-pricing-line__container"
                        widthProp="18.75rem"
                        :activator="$refs.icon"
                        top
                        alignCenter
                    >
                        <slot
                            name="explanation"
                        >
                        </slot>
                    </PopupBasic>
                </div>
            </div>
        </div>
        <div class="o-pricing-line__plans">
            <div
                v-for="plan in plans"
                :key="plan.id"
                class="flex-1 text-center"
            >
                <slot
                    :plan="plan"
                ></slot>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: 'PricingLine',
    components: {

    },
    mixins: [
    ],
    props: {
        plans: {
            type: Array,
            required: true,
        },
        showInfo: Boolean,
    },
    data() {
        return {
            popupOn: false,
        };
    },
    computed: {

    },
    methods: {

    },
    created() {

    },
    mounted() {
        window.addEventListener('keyup', (event) => {
            if (event.keyCode === 27) {
                this.popupOn = false;
            }
        });
    },
};
</script>

<style>

.o-pricing-line {
    @apply
        flex
        items-center
        p-4
    ;

    &__name {
        width: 300px;

        @apply
            flex
            items-start
            justify-between
        ;
    }

    &__plans {
        @apply
            flex
            flex-1
        ;
    }

    &__info {
        @apply
            text-gray-600
        ;

        /*
        &:hover + .o-pricing-line__explanation {
            @apply
                block
            ;
        }
         */
    }

    &__container {
        @apply
            border
            border-gray-400
            border-solid
            leading-snug
            p-4
            rounded-lg
            text-center
            text-gray-700
            text-sm
        ;
    }
}

</style>
