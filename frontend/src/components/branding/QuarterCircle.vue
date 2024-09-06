<template>
    <div
        class="c-quarter-circle"
        :style="pointStyle"
    >
        <slot>
        </slot>
    </div>
</template>

<script>

export default {
    name: 'QuarterCircle',
    components: {

    },
    mixins: [
    ],
    props: {
        point: {
            type: String,
            default: 'top-right',
        },
        oppositeRadius: {
            type: String,
            default: '0',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        pointStyle() {
            const size = '100%';

            let declaration;
            if (this.point === 'bottom-right') {
                declaration = `${size} 0 ${this.oppositeRadius} 0`;
            } else if (this.point === 'bottom-left') {
                declaration = `0 ${size} 0 ${this.oppositeRadius}`;
            } else if (this.point === 'top-left') {
                declaration = `${this.oppositeRadius} 0 ${size} 0`;
            } else if (this.point === 'top-right') {
                declaration = `0 ${this.oppositeRadius} 0 ${size}`;
            }
            return { borderRadius: declaration };
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*
.c-quarter-circle {
}
 */

</style>
