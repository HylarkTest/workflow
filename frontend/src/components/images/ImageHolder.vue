<template>
    <img
        class="c-image-holder bg-cm-100 h-full"
        :class="imageClasses"
        :src="image"
    >
</template>

<script>

export default {
    name: 'ImageHolder',
    components: {

    },
    mixins: [
    ],
    props: {
        image: {
            type: String,
            required: true,
        },
        contain: Boolean,
        shape: {
            type: String,
            default: '',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        containClass() {
            return this.contain ? 'object-contain' : 'object-cover w-full';
        },
        imageClasses() {
            return [
                this.containClass,
                this.shape ? this.shapeClass : '',
            ];
        },
        shapeClass() {
            const camelShape = _.camelCase(this.shape);
            return `clip-${camelShape}`;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>
/*.c-image-holder {

}*/
</style>
