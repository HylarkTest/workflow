<template>
    <div
        class="o-home-dive bg-secondary-100 rounded-xl"
    >
        <div
            class="text-secondary-700 font-bold text-xl px-4 py-2"
        >
            <ExpandCollapseButton
                class="mr-3"
                :isExpanded="isExpandOpen"
                buttonColor="button-secondary--medium"
                @toggleExpandCollapse="toggleExpand"
            >
            </ExpandCollapseButton>
            Dive back in
        </div>
        <div
            v-show="isExpandOpen"
            class="p-4"
        >
            <HomeDiveItem
                v-for="item in recentItems"
                :key="item.id"
                class="mb-1 last:mb-0 bg-cm-00 rounded-lg px-4 py-1"
                :item="item.subject"
                :historyItem="item"
            >
            </HomeDiveItem>
        </div>
    </div>
</template>

<script setup>

import {
    ref,
} from 'vue';

import HomeDiveItem from './HomeDiveItem.vue';
import ExpandCollapseButton from '@/components/buttons/ExpandCollapseButton.vue';

defineProps({
    recentItems: {
        type: Array,
        required: true,
    },
});

// const emit = defineEmits([
// ]);

const isExpandOpen = ref(true);

const toggleExpand = () => {
    isExpandOpen.value = !isExpandOpen.value;
};

</script>

<style scoped>

/* .o-home-dive {

} */

</style>
