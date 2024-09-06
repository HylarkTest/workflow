<template>
    <EditFoundation
        class="o-feature-edit"
        :hideHeader="true"
        :tabs="tabs"
        :selectedTab="selectedTab"
        :selectedTabHeader="selectedTabHeader"
        tabComponent="IconVertical"
        tabClasses="p-0 rounded-b-xl"
        @selectTab="selectTab"
    >
        <component
            :is="selectedComponent"
            :page="page"
        >
        </component>
    </EditFoundation>
</template>

<script setup>

import {
    computed,
    ref,
    toRefs,
} from 'vue';

import EditFoundation from './EditFoundation.vue';
import FeatureEditMarkers from './FeatureEditMarkers.vue';

import { _t } from '@/i18n.js';

const props = defineProps({
    defaultTab: {
        type: String,
        default: '',
    },
    page: {
        type: Object,
        required: true,
    },
});

const { defaultTab } = toRefs(props);

const tabs = [
    {
        name: _t('customizations.tabs.markers.name'),
        icon: 'fal fa-tags',
        subtitle: _t('customizations.tabs.markers.subtitleFeature'),
        value: 'MARKERS',
        component: FeatureEditMarkers,
    },
];

const selectedTab = ref(defaultTab.value || tabs[0].value);

const selectedTabHeader = computed(() => {
    return `customizations.tabs.${_.camelCase(selectedTab.value)}.name`;
});

const selectedComponent = computed(() => {
    const selected = tabs.find((tab) => tab.value === selectedTab.value);
    return selected.component;
});

function selectTab(tab) {
    selectedTab.value = tab.value;
}

// const emit = defineEmits([
// ]);

</script>

<style scoped>
/* .o-feature-edit {

} */
</style>
