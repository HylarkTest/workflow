<template>
    <img
        :alt="alt"
        class="c-bird-image"
        :src="birdImage"
    >
</template>

<script>

export default {
    name: 'BirdImage',
    components: {

    },
    mixins: [
    ],
    props: {
        whichBird: {
            type: String,
            required: true,
        },
        altProp: {
            type: String,
            default: '',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        birdImage() {
            return `/branding/${this.whichBird}`;
        },
        alt() {
            return this.altProp || 'The Hylark bird, Marty';
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-bird-image {

} */

</style>
