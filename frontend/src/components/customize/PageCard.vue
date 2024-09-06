<template>
    <ButtonEl
        class="o-page-card hover:shadow-lg hover:shadow-primary-300/50"
        :class="[cardClass, deletedClass]"
        @click="openPageEdit('PAGE')"
    >
        <div class="flex self-end items-center">
            <TemplateTags
                :dataValue="{ name: pageTypeName }"
                :container="{ style: pageTypeSmall }"
            >
            </TemplateTags>

            <RouterLink
                :to="pageLink"
                class="button--xs button-primary--light"
                title="Go to page"
                @click.stop
            >
                <i
                    class="fa-regular fa-square-arrow-up-right text-primary-500 text-xs"
                >
                </i>
            </RouterLink>
        </div>

        <div
            class="circle-center h-10 w-10 bg-primary-200 mb-1"
        >
            <i
                class="fa-duotone"
                :class="page.symbol"
                :style="duotoneColors(accentColor)"
            >
            </i>
        </div>

        <div class="text-smbase font-semibold">
            {{ page.name }}
        </div>

        <ButtonEl
            v-if="pageMapping"
            class="o-page-card__blueprint"
            @click.stop="openPageEdit('MAPPING')"
        >
            <div class="flex items-center">
                <div class="h-5 w-5 circle-center text-xxs text-primary-600 bg-primary-100 mr-1">
                    <i
                        class="far fa-compass-drafting"
                    >
                    </i>
                </div>
                <span
                    class="uppercase font-semibold text-cm-400 mr-1"
                >
                    {{ $t('common.blueprint') }}:
                </span>
            </div>

            <span class="o-page-card__mapping">
                {{ pageMapping.name }}
            </span>
        </ButtonEl>

        <div
            v-if="isSubset"
            class="text-xs flex items-center"
        >
            <label class="uppercase text-gray-400 font-semibold">
                Filtered by:
            </label>
            <div class="flex items-center ml-1">
                <span
                    class="mr-2"
                >
                    {{ filterName }} - {{ $t('labels.' + operator) }}:
                </span>
                <SubsetValue
                    :fieldValue="fieldValue"
                    :markerFiltersLength="markerFiltersLength"
                    :fieldFiltersLength="fieldFiltersLength"
                    :markerWithValue="markerWithValue"
                    :markerGroupWithValue="markerGroupWithValue"
                >
                </SubsetValue>
            </div>
        </div>
    </ButtonEl>
</template>

<script>

import interactsWithPageItem from '@/vue-mixins/customizations/interactsWithPageItem.js';

export default {
    name: 'PageCard',
    components: {
    },
    mixins: [
        interactsWithPageItem,
    ],
    props: {
        cardClass: {
            type: String,
            default: '',
        },
        isBeingDeleted: Boolean,
        // space: {
        //     type: Object,
        //     required: true,
        // },
    },
    emits: [
        'openPageEdit',
    ],
    data() {
        return {

        };
    },
    computed: {
        deletedClass() {
            return this.isBeingDeleted ? 'unclickable' : '';
        },
        pageLink() {
            return this.page.route;
        },
    },
    methods: {
    },
    created() {

    },
};
</script>

<style scoped>

.o-page-card {
    transition: 0.2s ease-in-out;
    @apply
        bg-cm-00
        flex
        flex-col
        items-center
        p-4
        rounded-xl
    ;

    &__blueprint {
        @apply
            flex
            items-start
            mt-1
            text-xs
        ;

        &:hover .o-page-card__mapping {
            text-decoration: underline;
        }
    }

    &__mapping {
        @apply
            mt-0.5
            text-cm-500
        ;
    }
}

</style>
