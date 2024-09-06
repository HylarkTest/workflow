<template>
    <div
        class="c-displayer-image centered"
        :class="sizeClass"
    >
        <ImageOrFallback
            class="font-bold text-secondary-600"
            :class="imageClasses"
            :imageClass="[displayClasses, cropClass]"
            :image="image"
            :contain="isContain"
            :name="name"
            :shape="shape"
        >
        </ImageOrFallback>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

import { squareImageShapes } from '@/core/display/displayerInstructions.js';

export default {
    name: 'DisplayerImage',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {
    },
    data() {
        return {
            typeKey: 'IMAGE',
        };
    },
    computed: {
        isContain() {
            return this.additional === 'preserveRatio';
        },
        imageClasses() {
            return [
                { 'bg-secondary-100': !this.image },
                this.containClass,
            ];
        },
        containClass() {
            // For ImageOrFallback outer element
            if (!this.image) {
                if (this.isSquareImageShape) {
                    return 'h-full';
                }
                return 'h-full w-full';
            }
            if (this.isSquareImageShape) {
                return 'h-full';
            }
            if (this.isContain) {
                return 'w-auto';
            }
            return 'h-full w-full';
        },
        image() {
            return this.displayFieldValue?.url;
        },
        sizeClass() {
            // This element, definition of 100%
            if (this.isInSpreadsheet) {
                return 'h-12 w-12';
            }
            if (this.sizeInstructions) {
                return this.sizeInstructions;
            }
            return 'h-10 w-10';
        },
        cropClass() {
            // For the image itself
            if (this.isSquareImageShape) {
                const split = _.split(this.sizeClass, ' ');
                const height = _.find(split, (s) => s.includes('h-'));
                const width = _.replace(height, 'h-', 'w-');
                return `${height} ${width}`;
            }
            return '';
        },
        shape() {
            return this.selectedCombo.shape;
        },
        isSquareImageShape() {
            return squareImageShapes.includes(this.shape);
        },
        name() {
            return this.item?.name;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-image {

} */

</style>
