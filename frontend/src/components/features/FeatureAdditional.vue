<template>
    <div class="c-feature-additional">
        <div
            v-if="hasAssociations"
            class="flex -m-1 justify-end flex-wrap"
        >
            <ConnectedRecord
                v-for="association in associations"
                :key="association.id"
                :item="association"
                :bgColor="bgColor"
                class="m-1"
                @click.stop
            >

            </ConnectedRecord>
        </div>
        <div
            v-if="tags && tags.length"
            class="flex -m-1 justify-end flex-wrap"
        >
            <TagBasic
                v-for="tag in tags"
                :key="tag.id"
                class="m-1"
                :tag="tag"
                :hideRemove="true"
            >
            </TagBasic>
        </div>
        <div
            v-if="statuses && statuses.length"
            class="flex -m-1 mt-1 justify-end flex-wrap"
        >
            <StatusBasic
                v-for="status in statuses"
                :key="status.id"
                class="m-1"
                :status="status"
            >
            </StatusBasic>
        </div>

        <div
            v-if="pipelines && pipelines.length"
            class="flex -m-1 mt-1 justify-end flex-wrap"
        >
            <PipelineBasic
                v-for="pipeline in pipelines"
                :key="pipeline.id"
                class="m-1"
                :pipeline="pipeline"
            >
            </PipelineBasic>
        </div>
    </div>
</template>

<script>

import { isActiveBaseCollaborative } from '@/core/repositories/baseRepository.js';

export default {
    name: 'FeatureAdditional',
    components: {
    },
    mixins: [
    ],
    props: {
        associations: {
            type: Array,
            required: true,
        },
        tags: {
            type: Array,
            required: true,
        },
        pipelines: {
            type: Array,
            required: true,
        },
        statuses: {
            type: Array,
            required: true,
        },
        bgColor: {
            type: String,
            default: 'white',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        hasAssociations() {
            return this.associations?.length;
        },
        isCollaborativeBase() {
            return isActiveBaseCollaborative();
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-feature-additional {

} */

</style>
