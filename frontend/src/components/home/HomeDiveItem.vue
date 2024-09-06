<template>
    <ButtonEl
        class="o-home-dive-item hover:shadow-md transition-2eio"
        @click="goToDataLocation"
    >
        <div class="flex justify-between">
            <div class="flex mr-2 min-w-0">
                <div
                    class="mr-2"
                >
                    <IconsComposition
                        v-if="showPageIcon"
                        sizeClass="w-6 h-6 text-sm"
                        roundedClass="rounded-md"
                        :iconsComposition="icons"
                    >
                    </IconsComposition>
                    <ImageName
                        v-else
                        :name="objName"
                        :image="firstImageUrl"
                        size="xs"
                        :hideFullName="true"
                    >
                    </ImageName>
                </div>

                <p class="text-sm text-cm-700 u-ellipsis">
                    {{ objName }}
                </p>
            </div>
            <div class="flex items-center gap-1">
                <div
                    class="o-home-dive-item__tag"
                    :class="mainTag.colorClasses"
                    :title="mainTag.title"
                >
                    <i
                        v-if="mainTag.icon"
                        class="fal mr-1"
                        :class="mainTag.icon"
                    >
                    </i>
                    {{ mainTag.label }}
                </div>
                <SpaceNameLabel
                    size="sm"
                    :spaceName="spaceName"
                >
                </SpaceNameLabel>
            </div>
        </div>
    </ButtonEl>
</template>

<script setup>
import {
// computed,
// ref,
    toRefs,
} from 'vue';
import IconsComposition from '@/components/assets/IconsComposition.vue';
import ImageName from '@/components/images/ImageName.vue';
import SpaceNameLabel from '@/components/display/SpaceNameLabel.vue';

import { useDataTypes } from '@/composables/useDataTypes.js';

const props = defineProps({
    // Doing it this way so that this can potentially be reused without the
    // history
    item: {
        type: Object,
        required: true,
    },
    historyItem: {
        type: [Object, null],
        default: null,
    },
});

const { item } = toRefs(props);

const {
    objName,
    icons,
    mainTag,
    goToDataLocation,
    firstImageUrl,
    spaceName,
    showPageIcon,
} = useDataTypes(item);

// const emit = defineEmits([
// ]);

</script>

<style scoped>

.o-home-dive-item {
    &__tag {
        padding: 1px 8px;

        @apply
            font-semibold
            rounded-full
            text-xxs
        ;
    }
}
</style>
