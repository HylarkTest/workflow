<template>
    <button
        class="c-expand-collapse-button__button button--xs"
        :class="buttonColor"
        type="button"
        @click="$emit('toggleExpandCollapse')"
    >
        <i
            class="c-expand-collapse-button__angle centered far"
            :class="expandCollapseIcon"
            :title="expandCollapseTitle"
        >
        </i>
    </button>
</template>

<script setup>

import {
    computed,
} from 'vue';

import { _t } from '@/i18n.js';

const props = defineProps({
    isExpanded: Boolean,
    buttonColor: {
        type: String,
        default: 'button-primary--medium',
    },
});

defineEmits([
    'toggleExpandCollapse',
]);

const expandCollapseIcon = computed(() => {
    return props.isExpanded ? 'fa-angle-up' : 'fa-angle-down';
});

const expandCollapseTitle = computed(() => {
    return props.isExpanded ? _t('common.collapseGroup') : _t('common.expandGroup');
});

</script>

<style scoped>
.c-expand-collapse-button {
    &__angle {
        @apply
            leading-4
            text-base
        ;
    }
}
</style>
