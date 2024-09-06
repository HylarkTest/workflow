<template>
    <div class="o-customize-space-blueprints">
        <div
            v-for="blueprint in blueprintsList"
            :key="blueprint.id"
            class="o-customize-space-blueprints__blueprint mb-4"
        >
            <div class="mb-2">
                <i
                    class="far fa-compass-drafting mr-2 text-primary-700"
                >
                </i>
                <ButtonEl
                    :component="'span'"
                    class="font-bold text-lg cursor-pointer"
                    @click="editBlueprint(blueprint)"
                >
                    {{ blueprint.name }}
                </ButtonEl>
            </div>
            <div class="italic text-cm-700 mb-1.5">
                {{ $t('customizations.pageWizard.review.usedInPages') }}:
            </div>
            <div
                v-for="page in blueprintPages(blueprint)"
                :key="page.id"
                class="ml-6 mb-1"
            >
                <i
                    class="fa-duotone fa-fw mr-2 text-primary-500"
                    :class="page.symbol"
                    :style="duotoneColors(accentColor)"
                >
                </i>
                <span class="text-cm-500">{{ page.name }}</span>
            </div>
        </div>
    </div>
</template>

<script>

import ButtonEl from '@/components/assets/ButtonEl.vue';

import providesColors from '@/vue-mixins/style/providesColors.js';

export default {
    name: 'CustomizeSpaceBlueprints',
    components: {
        ButtonEl,
    },
    mixins: [
        providesColors,
    ],
    props: {
        pages: {
            type: Array,
            required: true,
        },
    },
    emits: [
        'openPageEdit',
    ],
    data() {
        return {

        };
    },
    computed: {
        blueprintsList() {
            const allBlueprints = this.pages.map((page) => page.mapping).filter((mapping) => mapping);
            const uniqueBluprints = _.uniqBy(allBlueprints, 'id');
            return uniqueBluprints;
        },
        pagesWithMappings() {
            return this.pages.filter((page) => page.mapping);
        },
        pagesGroupedByMapping() {
            return _.groupBy(this.pagesWithMappings, 'mapping.id');
        },
    },
    methods: {
        blueprintPages(blueprint) {
            return this.pagesGroupedByMapping[blueprint.id];
        },
        editBlueprint(blueprint) {
            this.$emit('openPageEdit', {
                page: null,
                selectedView: 'MAPPING',
                blueprint,
            });
        },
    },
    created() {

    },
};

</script>

<style scoped>
.o-customize-space-blueprints {
    &__blueprint {
        @apply
            bg-white
            mb-4
            px-4
            py-2
            rounded-lg
        ;
    }
}
</style>
