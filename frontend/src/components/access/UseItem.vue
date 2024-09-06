<template>
    <ButtonEl
        class="o-use-item"
        :class="{ 'o-use-item--selected': isSelected }"
    >
        <div class="o-use-item__image-container">
            <img
                class="o-use-item__image"
                :src="imageSrc"
            />

            <div
                v-if="isSelected"
                class="o-use-item__check circle-center"
                :title="$t('common.selected')"
            >
                <i
                    class="fas fa-check"
                >
                </i>
            </div>

            <div
                class="o-use-item__title"
            >
                <div
                    class="bg-cm-950 opacity-60 w-full h-full min-h-full absolute rounded-r-full"
                >
                    &nbsp;
                </div>

                <p
                    v-md-text="title"
                    class="relative z-over py-2 px-4 m-0"
                >
                </p>

            </div>
        </div>

        <div class="px-3 pb-3 pt-12 -mt-8">
            <p
                v-md-text="greatFor"
                class="o-use-item__paragraph text-cm-500"
            >
            </p>

            <div
                v-if="tags?.length"
                class="flex gap-2 flex-wrap mt-2 justify-end text-xs font-medium"
            >
                <div
                    v-for="tag in tags"
                    :key="tag"
                    class="bg-primary-100 rounded-full py-1 px-3 text-primary-600"
                >
                    {{ $t(`registration.uses.tags.${tag}`) }}
                </div>
            </div>
        </div>
    </ButtonEl>
</template>

<script>
// This is a common component but as UseItem is also used on the landing page
// we should import it manually so we don't need to import all common components
// just so this one component will work.
import ButtonEl from '@/components/assets/ButtonEl.vue';

export default {
    name: 'UseItem',
    components: {
        ButtonEl,
    },
    mixins: [
    ],
    props: {
        use: {
            type: Object,
            required: true,
        },
        isSelected: Boolean,
        base: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        formattedVal() {
            return _.camelCase(this.use.val);
        },
        baseType() {
            return this.base.baseType;
        },
        baseTypeFormatted() {
            return _.camelCase(this.baseType);
        },
        imageSrc() {
            return `${import.meta.env.VITE_API_URL}/images/stockUses/${this.formattedVal}.jpg`;
        },
        title() {
            return this.$t(`registration.uses.headers.${this.baseTypeFormatted}.${this.formattedVal}`);
        },
        greatFor() {
            return this.$t(`registration.uses.greatFor.${this.baseTypeFormatted}.${this.formattedVal}`);
        },
        descriptionPath() {
            return `registration.uses.descriptions.${this.formattedVal}`;
        },
        tags() {
            return this.use.tags;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style>

.o-use-item {
    width: 280px;

    @apply
        border
        border-solid
        border-transparent
        rounded-xl
        shadow-lg
    ;

    &--selected {
        @apply
            border-primary-600
        ;
    }

    &:hover {
        .o-use-item__image {
            transform: scale(1.03);

            @apply
                opacity-75
                rounded-xl
            ;
        }
    }

    &__image-container {
        line-height: 0;
        @apply
            overflow-hidden
            relative
            rounded-xl
            shadow-md
        ;
    }

    &__image {
        height: 140px;
        object-fit: cover;
        transition: 0.2s ease-in-out;

        @apply
            max-w-full
            rounded-xl
            w-full
        ;
    }

    &__check {
        right: 6px;
        top: 6px;

        @apply
            absolute
            bg-primary-600
            h-8
            text-cm-00
            w-8
            z-over
        ;
    }

    &__title {
        @apply
            absolute
            bottom-2
            left-0
            text-cm-00
            text-smbase
            w-11/12
        ;

        strong {
            @apply
                font-semibold
            ;
        }
    }

    &__paragraph {
        font-size: 14px;

        @apply
            leading-snug
        ;

        strong {
            @apply
                font-semibold
                text-primary-700
            ;
        }
    }

    /*&__great {
        @apply
            font-semibold
            relative
        ;

        &::after {
            content: '';
            @apply
                absolute
                bg-secondary-600
                -bottom-0.5
                h-1
                right-0
                rounded-full
                w-full
            ;
        }
    }*/
}

</style>
