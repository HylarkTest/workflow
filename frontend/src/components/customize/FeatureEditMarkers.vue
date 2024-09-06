<template>
    <div
        class="o-feature-edit-markers"
        :class="{ unclickable: processing }"
    >
        <LoaderFetch
            v-if="isLoading"
            :isFull="true"
            class="py-10"
            :sphereSize="50"
        >
        </LoaderFetch>

        <div v-else>
            <SpacesList
                :spaces="spaces"
            >
                <template #space="{ space }">
                    <div
                        v-for="type in markerTypes"
                        :key="type"
                        class="w-full lg:flex-1"
                    >
                        <MappingEditMarkerList
                            :type="type"
                            :mappingName="'feature'"
                            :markerGroups="getAllMarkerGroups(type)"
                            :activatedMarkerGroups="getActivatedMarkerGroups(type, space)"
                            :emitSingleMarker="true"
                            :disableRemoveConfirmation="true"
                            @updateMarkerGroups="updateMarker($event, type, space)"
                        >
                        </MappingEditMarkerList>
                    </div>
                </template>
            </SpacesList>
        </div>
    </div>
</template>

<script setup>

import {
    computed,
    ref,
} from 'vue';

import { useQuery } from '@vue/apollo-composable';

import MappingEditMarkerList from './MappingEditMarkerList.vue';
import SpacesList from './SpacesList.vue';

import { arrReplaceOrPushId } from '@/core/utils.js';

import TAG_GROUPS from '@/graphql/markers/queries/MarkerGroups.gql';
import SPACES from '@/graphql/spaces/queries/Spaces.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';

import { createApolloForm } from '@/core/plugins/formlaPlugin.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';
import { updateMarkerGroup } from '@/core/repositories/markerRepository.js';

import { debouncedSaveFeedback } from '@/core/uiGenerators/userFeedbackGenerators.js';

const { result: tagGroupsResult, loading: loadingTagGroups } = useQuery(TAG_GROUPS);

const tagGroups = computed(() => {
    return initializeConnections(tagGroupsResult.value);
});

const { result: spacesResult, loading: loadingSpaces } = useQuery(SPACES);

const spaces = computed(() => {
    return initializeConnections(spacesResult.value).spaces;
});

const isLoading = computed(() => {
    return loadingTagGroups.value || loadingSpaces.value;
});

const props = defineProps({
    page: {
        type: Object,
        required: true,
    },
});

const processing = ref(false);

const markerTypes = ['TAG', 'STATUS', 'PIPELINE'];

const markerGroups = computed(() => {
    return tagGroups.value.markerGroups;
});

function getAllMarkerGroups(type) {
    return _.filter(markerGroups.value, { type });
}

function getActivatedMarkerGroups(type, space) {
    const groups = getAllMarkerGroups(type);
    return groups.filter((group) => {
        const usedByFeatures = group.usedByFeatures;
        const spaceObj = usedByFeatures.find((usedByFeature) => {
            return usedByFeature.space.id === space.id;
        });
        return spaceObj?.features.includes(props.page.type);
    });
}

function getMappedUsedByFeatures(marker) {
    return marker.usedByFeatures.map(({ space, features }) => {
        return {
            spaceId: space.id,
            features,
        };
    });
}

function getNewFeatures(spaceFeatures) {
    let newFeatures = [];
    const alreadySelected = spaceFeatures.includes(props.page.type);
    if (alreadySelected) {
        newFeatures = spaceFeatures.filter((feature) => feature !== props.page.type);
    } else {
        newFeatures = [...spaceFeatures, props.page.type];
    }
    return newFeatures;
}

async function saveMarkerGroup(markerObj) {
    try {
        processing.value = true;
        const apolloForm = createApolloForm(
            baseApolloClient(),
            markerObj
        );
        await updateMarkerGroup(apolloForm);
        debouncedSaveFeedback();
    } finally {
        processing.value = false;
    }
}

function updateMarker(marker, type, updatedSpace) {
    // Map usedByFeatures to format expected by b-e
    const mappedUsedByFeatures = getMappedUsedByFeatures(marker);

    // Find current features in this space for this marker, add or remove selected feature as needed
    const foundSpace = mappedUsedByFeatures.find(({ spaceId }) => spaceId === updatedSpace.id);
    const spaceFeatures = foundSpace.features;

    const newFeatureObj = {
        spaceId: updatedSpace.id,
        features: getNewFeatures(spaceFeatures),
    };
    // Replace current features array with new one
    const val = arrReplaceOrPushId(mappedUsedByFeatures, updatedSpace.id, newFeatureObj, 'spaceId');

    const savingObj = {
        id: marker.id,
        usedByFeatures: val,
    };

    saveMarkerGroup(savingObj);
}

// const emit = defineEmits([
// ]);

</script>

<style scoped>
.o-feature-edit-markers {
    &__space {
        @apply
            border-b
            border-cm-200
            border-solid
            mb-2
            pb-2
        ;

        &:last-child {
            @apply
                border-none
                mb-0
                pb-0
            ;
        }
    }
}
</style>
