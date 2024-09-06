<template>
    <div class="o-spaces-list">
        <div class="flex justify-end mb-2">
            <ExpandCollapseAllButton
                :allSectionsExpanded="allSpacesExpanded"
                @toggleAllOpenState="toggleAllOpenState"
            >
            </ExpandCollapseAllButton>
        </div>
        <div
            v-for="space in props.spaces"
            :key="space.id"
            class="o-spaces-list__space"
        >
            <ButtonEl
                class="flex items-center mb-2 cursor-pointer"
                @click="toggleOpenState(space.id)"
            >
                <ExpandCollapseButton
                    class="mr-3"
                    :isExpanded="isOpen(space.id)"
                >
                </ExpandCollapseButton>
                <h3 class="header-2 text-cm-400">
                    {{ space.name }}
                </h3>
            </ButtonEl>
            <div
                v-show="isOpen(space.id)"
                class="flex flex-wrap gap-4"
            >
                <slot
                    name="space"
                    :space="space"
                >
                </slot>
            </div>
        </div>
    </div>
</template>

<script setup>

import {
    ref,
    computed,
} from 'vue';

import ExpandCollapseAllButton from '@/components/buttons/ExpandCollapseAllButton.vue';
import ExpandCollapseButton from '@/components/buttons/ExpandCollapseButton.vue';

import { arrRemove } from '@/core/utils.js';

const props = defineProps({
    spaces: {
        type: Array,
        required: true,
    },
});

const spaceIds = computed(() => {
    return props.spaces.map((space) => space.id);
});

const closedSpaces = ref([]);

function isOpen(spaceId) {
    const isClosed = closedSpaces.value.includes(spaceId);
    return !isClosed;
}

function toggleOpenState(spaceId) {
    // If space is open, close it, i.e. push spaceId to closedSpaces. If opening, remove spaceId from closedSpaces.
    if (isOpen(spaceId)) {
        closedSpaces.value.push(spaceId);
    } else {
        closedSpaces.value = arrRemove(closedSpaces.value, spaceId);
    }
}

const allSpacesExpanded = computed(() => {
    return closedSpaces.value.length === 0;
});

function toggleAllOpenState() {
    // If all spaces are opened, close all, i.e. set closedSpaces to incl. all ids. If opening all, clear closedSpaces.
    if (allSpacesExpanded.value) {
        closedSpaces.value = [...spaceIds.value];
    } else {
        closedSpaces.value = [];
    }
}

</script>

<style scoped>

.o-spaces-list {
    @apply
        pb-4
    ;

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
