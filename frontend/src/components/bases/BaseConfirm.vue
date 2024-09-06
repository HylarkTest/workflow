<template>
    <div class="o-base-confirm">
        <!-- <h2
            class="o-base-confirm__header"
        >
            {{ $tc('registration.confirm.yourSpaces', spacesLength) }}
        </h2>
 -->
        <div class="o-base-confirm__content">
            <div
                class="o-base-confirm__side mb-4"
            >
                <button
                    v-for="space in savedSpaces"
                    :key="space.id"
                    type="button"
                    class="o-base-confirm__space u-text"
                    :class="{ 'o-base-confirm__space--active': space.id === selectedSpace.id }"
                    @click="setSelectedSpace(space)"
                >
                    {{ space.name }}
                </button>
            </div>

            <div
                class="o-base-confirm__container shadow-primary-600/20"
            >
                <SpaceVisual
                    v-if="spaceHasPages"
                    :space="selectedSpace"
                    :base="base"
                    @updateSpaceAfterMerge="updateSpaceAfterMerge"
                    @resetMerge="resetMerge"
                >
                </SpaceVisual>
            </div>
        </div>
    </div>
</template>

<script>

import SpaceVisual from '@/components/product/SpaceVisual.vue';

export default {
    name: 'BaseConfirm',
    components: {
        SpaceVisual,
    },
    mixins: [
    ],
    props: {
        base: {
            type: Object,
            required: true,
        },
        baseStructure: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:base',
    ],
    data() {
        return {
            selectedSpace: null,
        };
    },
    computed: {
        savedSpaces() {
            return this.baseStructure.spaces || [];
        },
        spacesLength() {
            return this.savedSpaces.length;
        },
        spaceHasPages() {
            return this.selectedSpace?.pages?.length;
        },
    },
    methods: {
        setSelectedSpace(space) {
            this.selectedSpace = space;
        },
        updateSpaceAfterMerge({ newPages, space }) {
            const newBase = _.cloneDeep(this.base);
            const spaceIndex = _.findIndex(newBase.spaces, { id: space.id });
            const spaceNewPages = newBase.spaces[spaceIndex].newPages;
            if (spaceNewPages) {
                newBase.spaces[spaceIndex].newPages = spaceNewPages.concat(newPages);
            } else {
                newBase.spaces[spaceIndex].newPages = newPages;
            }
            this.$emit('update:base', newBase);
        },
        resetMerge(space) {
            const newBase = _.cloneDeep(this.base);
            const spaceIndex = _.findIndex(newBase.spaces, { id: space.id });
            delete newBase.spaces[spaceIndex].newPages;
            this.$emit('update:base', newBase);
        },
    },
    watch: {
        savedSpaces: {
            immediate: true,
            handler(spaces) {
                this.setSelectedSpace(spaces[0]);
            },
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-base-confirm {
    &__content {
        @apply
            w-full
        ;

        @media (min-width: 768px) {
            & {
                @apply
                    flex
                ;
            }
        }
    }

    &__side {
        width: 150px;
    }

    /*&__header {
        @apply
            font-bold
            mb-4
            text-xl
        ;
    }*/

    &__space {
        padding-bottom: 4px;
        padding-top: 4px;

        @apply
            block
            font-semibold
            px-4
            rounded-full
        ;

        &--active {
            @apply
                bg-azure-100
                text-primary-600
            ;
        }
    }

    &__container {
        @apply
            flex-1
            p-8
            rounded-xl
            shadow-lg
        ;

        @media (min-width: 800px) {
            & {
                @apply
                    ml-8
                ;
            }
        }
    }
}

</style>
