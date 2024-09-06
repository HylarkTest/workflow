<template>
    <div
        class="c-half-circle"
        :style="halfStyle"
    >
        <slot>
        </slot>
    </div>
</template>

<script>

export default {
    name: 'HalfCircle',
    components: {

    },
    mixins: [
    ],
    props: {
        flat: {
            type: String,
            default: 'TOP',
            validator(val) {
                return ['TOP', 'BOTTOM', 'LEFT', 'RIGHT'].includes(val);
            },
        },
    },
    data() {
        return {

        };
    },
    computed: {
        halfStyle() {
            const size = '100%';
            const half = '50%';

            let declaration;
            if (this.flat === 'BOTTOM') {
                declaration = `${half} ${half} 0 0 / ${size} ${size} 0 0`;
            } else if (this.flat === 'TOP') {
                declaration = `0 0 ${half} ${half} / 0 0 ${size} ${size}`;
            } else if (this.flat === 'RIGHT') {
                declaration = `${size} 0 0 ${size} / ${half} 0 0 ${half}`;
            } else if (this.flat === 'LEFT') {
                declaration = `0 ${size} ${size} 0 / 0 ${half} ${half} 0`;
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
.c-half-circle {
}
 */

</style>
