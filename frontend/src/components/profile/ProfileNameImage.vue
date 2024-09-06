<template>
    <ImageName
        :class="sizeClass"
        :image="profileAvatar"
        :name="profileName"
        :vertical="vertical"
        :hideFullName="hideFullName"
        :size="size"
        :colorName="profileColorName"
        v-bind="$attrs"
    >
        <template
            v-if="showEmail"
            #extra
        >
            <p
                class="c-profile-name-image__email text-cm-500 py-1"
            >
                {{ profileEmail }}
            </p>
        </template>

        <template
            v-if="$slots.icon"
            #icon
        >
            <slot
                name="icon"
            >
            </slot>
        </template>
    </ImageName>
</template>

<script>

import ImageName from '@/components/images/ImageName.vue';

import interactsWithBasicProfileInfo from '@/vue-mixins/interactsWithBasicProfileInfo.js';

export default {
    name: 'ProfileNameImage',
    components: {
        ImageName,
    },
    mixins: [
        interactsWithBasicProfileInfo,
    ],
    props: {
        hideFullName: Boolean,
        size: {
            type: String,
            default: 'md',
            validator(val) {
                return ['lg', 'md', 'sm', 'xs'].includes(val);
            },
        },
        showEmail: Boolean,
        vertical: Boolean,
        colorName: {
            type: String,
            default: '',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        sizeClass() {
            return `c-profile-name-image--${this.size}`;
        },
        profileColorName() {
            return this.colorName
                || (this.profile?.baseType && this.profileIsCollaborative ? 'azure' : 'gold');
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-profile-name-image {
    &__email {
        @apply
            leading-none
            text-xssm
        ;
    }

    &--lg {
        .c-profile-name-image__email {
            @apply
                text-smbase
            ;
        }
    }

    &--sm {
        .c-profile-name-image__email {
            @apply
                text-xs
            ;
        }
    }

    &__additional {
        @apply
            absolute
            bg-cm-00
            border
            border-secondary-600
            border-solid
            -bottom-1.5
            h-4
            -right-1.5
            rounded-full
            text-xxxs
            w-4
            z-over
        ;
    }
}

</style>
