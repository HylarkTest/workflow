<template>
    <div
        class="c-triangle-box"
        :class="positioningClasses"
    >
        <div
            class="relative flex flex-col"
            :class="[roundedClass, bgColorClass]"
        >
            <div
                class="rounded rotate-45 absolute self-center"
                :class="bgColorClass"
                :style="triangleStyle"
            >

            </div>

            <slot>
            </slot>
        </div>
    </div>
</template>

<script>

export default {
    name: 'TriangleBox',
    components: {

    },
    mixins: [
    ],
    props: {
        bgColorClass: {
            type: String,
            default: 'bg-secondary-200',
        },
        roundedClass: {
            type: String,
            default: 'rounded-lg',
        },
        triangleSize: {
            type: Number,
            default: 16,
        },
        positioningClasses: {
            // For if position absolute or other positioning required
            type: String,
            default: '',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        triangleStyle() {
            return {
                width: `${this.triangleSize}px`,
                height: `${this.triangleSize}px`,
                top: `-${this.triangleSize / 2}px`,
            };
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-triangle-box {

} */

</style>
