<template>
    <div class="o-filters-content">
        <!-- Main filters sections, such as markers, custom fields, etc. -->
        <div
            v-for="filterGroup in filteredFilterables"
            :key="filterGroup.val"
            class="mb-5 last:mb-0"
        >
            <!-- Possible filter options in each main section -->
            <div
                v-for="option in filterGroup.options"
                :key="option.val"
                class="mb-4 last:mb-0"
            >
                <FilterOptionSection
                    :option="option"
                    :discreteFilters="discreteFilters"
                    :filterGroup="filterGroup"
                    @selectFilter="$emit('selectFilter', $event)"
                >
                </FilterOptionSection>
            </div>
        </div>
    </div>
</template>

<script setup>

// import {
//     computed,
//     ref,
// } from 'vue';

import FilterOptionSection from './FilterOptionSection.vue';

defineProps({
    filteredFilterables: {
        type: Array,
        required: true,
    },
    discreteFilters: {
        type: [Object, null],
        default: null,
    },
});

defineEmits([
    'selectFilter',
]);

</script>

<style scoped>
/* .o-filters-content {

} */
</style>
