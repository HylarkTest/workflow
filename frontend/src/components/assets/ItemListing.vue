<template>
    <ButtonEl
        class="o-item-listing hover:bg-cm-100 rounded-md transition-2eio"
        @click.stop.prevent="goToDataLocation"
    >
        <div class="flex justify-between items-center">

            <div class="items-center flex min-w-0">
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
                        :image="imageObjUrl"
                        size="xs"
                        :hideFullName="true"
                    >
                    </ImageName>
                </div>

                <span
                    class="text-sm u-ellipsis"
                >
                    {{ objName }}
                </span>
            </div>

            <span
                v-if="isNew"
                class="text-xxs text-primary-700 ml-1 bg-primary-200 rounded px-1 py-px font-semibold"
            >
                {{ $t('common.new') }}
            </span>
        </div>
    </ButtonEl>
</template>

<script setup>

import {
    computed,
    toRefs,
} from 'vue';

import IconsComposition from '@/components/assets/IconsComposition.vue';
import ImageName from '@/components/images/ImageName.vue';

import { useDataTypes } from '@/composables/useDataTypes.js';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    pageContext: {
        type: Object,
        required: true,
    },
});

const { item, pageContext } = toRefs(props);

const {
    objName,
    icons,
    goToDataLocation,
    imageObjUrl,
    showPageIcon,
} = useDataTypes(item, pageContext);

// const emit = defineEmits([
// ]);

const isNew = computed(() => {
    return item.value.createdAt !== item.value.updatedAt;
});

</script>

<style scoped>

/*.o-item-listing {

} */

</style>
