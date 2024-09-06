<template>
    <div class="c-type-image">
        <ImageOrFallback
            v-if="imageUrl || name"
            class="w-12 h-12"
            imageClass="rounded-lg"
            :image="imageUrl"
            :name="name"
        >
        </ImageOrFallback>

        <IconsComposition
            v-else
            sizeClass="w-12 h-12 text-lg"
            :iconsComposition="icons"
        >
        </IconsComposition>
    </div>
</template>

<script>

import IconsComposition from '@/components/assets/IconsComposition.vue';

export default {
    name: 'TypeImage',
    components: {
        IconsComposition,
    },
    mixins: [
    ],
    props: {
        imageUrl: {
            type: String,
            default: '',
        },
        icons: {
            type: Array,
            default: () => ([]),
        },
        name: {
            type: String,
            default: '',
        },
    },
    data() {
        return {

        };
    },
    computed: {
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-type-image {

} */

</style>
