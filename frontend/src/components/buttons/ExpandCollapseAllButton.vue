<template>
    <button
        class="button--xs button-primary--medium"
        type="button"
        @click="$emit('toggleAllOpenState')"
    >
        {{ expandCollapseAllText }}
    </button>
</template>

<script setup>

import {
    computed,
} from 'vue';

import { _t } from '@/i18n.js';

const props = defineProps({
    allSectionsExpanded: Boolean,
});

defineEmits([
    'toggleAllOpenState',
]);

const expandCollapseAllText = computed(() => {
    return props.allSectionsExpanded ? _t('common.collapseAll') : _t('common.expandAll');
});

</script>

<style scoped>
/* .o-expand-collapse-all-button {

} */
</style>
