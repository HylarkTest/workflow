<template>
    <div class="o-customize-space-pages">
        <div
            v-for="folder in spaceFolders"
            :key="folder.folder"
            class="mb-6"
        >
            <SpacePageList
                :folder="folder"
                :pageBeingDeleted="pageBeingDeleted"
                @openPageEdit="$emit('openPageEdit', $event)"
            >
            </SpacePageList>
        </div>
    </div>
</template>

<script setup>

// import {
//     computed,
//     ref,
// } from 'vue';

import SpacePageList from './SpacePageList.vue';

defineProps({
    spaceFolders: {
        type: Array,
        required: true,
    },
    pageBeingDeleted: {
        type: [Object, null],
        default: null,
    },
});

defineEmits([
    'openPageEdit',
]);

</script>

<style scoped>
/* .o-customize-space-pages {

} */
</style>
