<template>
    <div
        class="c-image-or-fallback centered max-h-full max-w-full h-full"
        :class="[imageClass, borderInstructions, shapeClass]"
        :title="title"
    >
        <ImageHolder
            v-if="image"
            class="max-h-full"
            :class="imageClass"
            :image="image"
            :shape="shape"
            :contain="contain"
        >
        </ImageHolder>
        <div
            v-else-if="icon"
            :class="iconClass"
        >
            <i
                class="far"
                :class="icon"
            >
            </i>
        </div>
        <template v-else-if="initials">
            {{ initials }}
        </template>
    </div>
</template>

<script>

import ImageHolder from './ImageHolder.vue';
import { getInitials } from '@/core/utils.js';

export default {
    name: 'ImageOrFallback',
    components: {
        ImageHolder,
    },
    mixins: [
    ],
    props: {
        image: {
            type: String,
            default: '',
        },
        name: {
            type: String,
            default: '',
        },
        icon: {
            type: String,
            default: '',
        },
        iconClass: {
            type: String,
            default: '',
        },
        imageClass: {
            type: [String, Object],
            default: '',
        },
        borderColorClass: {
            type: String,
            default: '',
        },
        shape: {
            type: String,
            default: '',
        },
        contain: Boolean,
        titleProp: {
            type: String,
            default: '',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        initials() {
            return this.name && getInitials(this.name);
        },
        hasNoImage() {
            return !this.image;
        },
        borderInstructions() {
            if (this.borderColorClass) {
                return `${this.borderColorClass} border border-solid`;
            }
            return '';
        },
        shapeClass() {
            const camelShape = _.camelCase(this.shape);
            return this.shape ? `clip-${camelShape}` : '';
        },
        title() {
            return this.titleProp || this.name;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>
/*.c-image-or-fallback {
}*/
</style>
