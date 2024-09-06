<template>
    <div
        class="c-image-name min-w-0"
        :class="[sizeClass, verticalClass]"
    >
        <div class="c-image-name__wrapper relative">
            <ImageOrFallback
                class="c-image-name__image transition-2eio relative"
                :class="imageFallbackClassesProp"
                :imageClass="imageClasses"
                :image="image"
                :name="name"
                :icon="icon"
                :titleProp="titleProp"
            >
            </ImageOrFallback>

            <slot
                name="icon"
            >
            </slot>
        </div>

        <div
            v-if="!hideFullName || $slots.extra"
            class="c-image-name__text overflow-hidden max-w-full"
        >
            <p
                v-if="!hideFullName"
                class="c-image-name__name u-ellipsis text-cm-600"
            >
                {{ name }}
            </p>

            <slot name="extra">
            </slot>
        </div>
    </div>
</template>

<script>

export default {
    name: 'ImageName',
    components: {

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
            required: true,
        },
        icon: {
            type: String,
            default: '',
        },
        size: {
            type: String,
            default: 'md',
            validator(val) {
                return ['lg', 'md', 'sm', 'xs', 'xxs', 'full'].includes(val);
            },
        },
        hideFullName: Boolean,
        vertical: Boolean,
        shapeClass: {
            type: String,
            default: 'rounded-md',
            validator(val) {
                return ['rounded-md', 'rounded-full'].includes(val);
            },
        },
        colorName: {
            type: String,
            default: 'secondary',
            validator(val) {
                return [
                    'secondary',
                    'primary',
                    'gold',
                    'azure',
                    'turquoise',
                    'sky',
                ].includes(val);
            },
        },
        isHoverable: Boolean,
        titleProp: {
            type: String,
            default: '',
        },
        imageFallbackClassesProp: {
            type: String,
            default: '',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        imageClasses() {
            let classes = `${this.shapeClass} text-${this.colorName}-700`;
            // Due to image transparency
            const bgClass = this.image
                ? ' bg-cm-200'
                : ` bg-${this.colorName}-200`;
            classes = classes.concat(bgClass);
            return classes;
        },
        sizeClass() {
            return `c-image-name--${this.size}`;
        },
        verticalClass() {
            return this.vertical ? 'c-image-name--vertical' : '';
        },
        imageFallbackClasses() {
            return [
                { 'hover:shadow-lg': this.isHoverable && this.hideFullName },
                this.imageFallbackClassesProp,
            ];
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style>

.c-image-name {
    @apply
        flex
        items-center
    ;

    &--vertical {
        @apply
            flex-col
        ;

        .c-image-name__image {
            @apply
                mb-2
                mr-0
            ;
        }

        .c-image-name__text {
            @apply
                text-center
            ;
        }
    }

    &__image {
        @apply
            font-semibold
            h-8
            shrink-0
            text-sm
            w-8
        ;
    }

    &__text {
        @apply
            flex-1
            ml-2
        ;
    }

    &__name {
        @apply
            font-semibold
            text-base
        ;
    }

    &--lg {
        .c-image-name__image {
            @apply
                h-12
                w-12
            ;
        }

        .c-image-name__name {
            @apply
                text-lg
            ;
        }
    }

    &--sm {
        .c-image-name__image {
            @apply
                h-7
                text-xs
                w-7
            ;
        }

        .c-image-name__name {
            @apply
                text-sm
            ;
        }
    }

    &--xs {
        .c-image-name__image {
            @apply
                h-6
                text-xxs
                w-6
            ;
        }

        .c-image-name__name {
            @apply
                text-xs
            ;
        }
    }

    &--xxs {
        .c-image-name__image {
            @apply
                h-5
                text-xxs
                w-5
            ;
        }

        .c-image-name__name {
            @apply
                text-xs
            ;
        }
    }

    &--full {
        @apply
            h-full
            w-full
        ;

        .c-image-name__wrapper {
            @apply
                h-full
                w-full
            ;
        }

        .c-image-name__image {
            font-size: inherit;

            @apply
                h-full
                w-full
            ;
        }

        .c-image-name__name {
            @apply
                text-inherit
            ;
        }
    }
}

</style>
