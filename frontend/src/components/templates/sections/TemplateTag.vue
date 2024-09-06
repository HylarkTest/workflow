<template>
    <div
        class="c-template-tag u-ellipsis"
        :class="tagClasses"
        :title="tag.name"
        :style="tagColors"
    >
        <span
            v-if="hasAbbreviate"
        >
            {{ abbreviatedName }}
        </span>

        <span
            v-else
        >
            {{ tag.name }}
        </span>

        <button
            v-if="showRemove"
            class="ml-1"
            type="button"
            @click="removeTag"
        >
            <i
                class="far fa-times"
            >
            </i>
        </button>
    </div>
</template>

<script>

import { getInitials } from '@/core/utils.js';

// Placeholder, need to use actual tag colors
const fillColor = {
    white: 'bg-cm-00',
    brandLight: 'brandLight',
    brandIntense: 'bg-primary-600',
};

const border = {
    none: '',
    thin: 'border border-solid',
    thick: 'border-2 border-solid',
};

const borderColor = {
    brandIntense: 'border-primary-600',
    brandLight: 'brandLight',
    dark: 'border-cm-700',
};

const textColor = {
    white: 'text-cm-00',
    dark: 'border-cm-800',
    brandIntense: 'brandIntense',
};

const weight = {
    regular: '',
    bold: 'font-semibold',
};

const textCase = {
    regular: '',
    uppercase: 'uppercase',
};

const abbreviatedSizes = {
    xs: 'h-5 w-5',
    sm: 'h-6 w-6',
    md: 'h-8 w-8',
    lg: 'h-10 w-10',
    xl: 'h-12 w-12',
};

const padding = {
    xs: 'px-3 py-0.5',
    sm: 'px-3 py-0.5',
    md: 'px-3 py-1',
    lg: 'px-4 py-1',
    xl: 'px-5 py-2',
};

const shapes = {
    rounded: {
        shape: 'rounded-full',
        ...padding,
    },
    rectangle: {
        shape: '',
        ...padding,
    },
    circle: {
        shape: 'rounded-full center font-semibold',
        abbreviate: true,
        ...abbreviatedSizes,
    },
    square: {
        shape: 'center font-semibold',
        abbreviate: true,
        ...abbreviatedSizes,
    },
};

// Text classes are smaller than the size value
const size = {
    xs: 'text-xxs',
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-base',
    xl: 'text-lg',
};

export default {
    name: 'TemplateTag',
    components: {

    },
    mixins: [
    ],
    props: {
        tag: {
            type: Object,
            required: true,
        },
        tagStyle: {
            type: Object,
            default: () => ({

            }),
        },
        showRemove: Boolean,
    },
    emits: [
        'removeTag',
    ],
    data() {
        return {

        };
    },
    computed: {
        shape() {
            return this.tagStyle.shape;
        },
        hasAbbreviate() {
            return shapes[this.shape] ? shapes[this.shape].abbreviate : false;
        },
        tagClasses() {
            return [
                this.shapeClasses,
                this.colouringClasses,
                this.sizeClasses,
                this.extraClasses,
                this.weightClasses,
                this.caseClasses,
            ];
        },
        shapeClasses() {
            return this.shape ? shapes[this.shape].shape : shapes.rounded.shape;
        },
        extraClasses() {
            return this.shape ? shapes[this.shape][this.size] : shapes.rounded[this.size];
        },
        colouringClasses() {
            return [this.fillColor, this.textColor, this.borderColor, this.border];
        },
        fillColor() {
            const fillVal = this.tagStyle.fillColor;
            return fillColor[fillVal] || fillVal || 'brandIntense';
            // return this.tagColors.backgroundColor;
        },
        textColor() {
            const textVal = this.tagStyle.textColor;
            return textColor[textVal] || textVal || 'cm-text-00';
        },
        borderColor() {
            if (this.border) {
                const borderVal = this.tagStyle.borderColor;
                return borderColor[borderVal] || borderVal;
            }
            return '';
        },
        border() {
            return border[this.tagStyle.border] || '';
        },
        size() {
            return this.tagStyle.size ? this.tagStyle.size : 'md';
        },
        sizeClasses() {
            return size[this.size];
        },
        weightClasses() {
            return weight[this.tagStyle.weight] || '';
        },
        caseClasses() {
            return textCase[this.tagStyle.case] || '';
        },
        abbreviatedName() {
            return getInitials(this.tag.name);
        },
        tagColors() {
            const style = {};

            const isFillIntense = this.fillColor === 'brandIntense';
            const isFillLight = this.fillColor === 'brandLight';

            const isBorderIntense = this.borderColor === 'brandIntense';
            const isBorderLight = this.borderColor === 'brandLight';

            const isTextIntense = this.textColor === 'brandIntense';
            const isTextLight = this.textColor === 'brandLight';

            if (isFillIntense || isFillLight) {
                const bgKey = isFillIntense ? 'color' : 'lightColor';
                style.backgroundColor = this.tag[bgKey];
            }
            if (isBorderIntense || isBorderLight) {
                const borderKey = isBorderIntense ? 'color' : 'lightColor';
                style.borderColor = this.tag[borderKey];
            }
            if (isTextIntense || isTextLight) {
                const textKey = isTextIntense ? 'color' : 'lightColor';
                style.color = this.tag[textKey];
            }
            return style;
        },
    },
    methods: {
        removeTag() {
            this.$emit('removeTag', this.tag);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*
.c-template-tag {

}
*/

</style>
