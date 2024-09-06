<template>
    <div
        v-if="potentialPages.length"
        class="o-potential-pages-section"
    >
        <slot name="header">
        </slot>

        <PotentialPagesList
            :potentialPages="potentialPages"
            @openPageCreation="$emit('openPageCreation', $event)"
        >
        </PotentialPagesList>
    </div>
</template>

<script>

import PotentialPagesList from './PotentialPagesList.vue';
import { availablePages } from '@/core/mappings/templates/pages.js';

const topPotentialPagesTemplateRefs = [
    'PROFESSIONAL_PROJECT',
    'WORK_CALENDAR',
    'MEETINGS',
    'GENERIC_TODOS',
    'JOB_POSITION',
    'CAREER_DOCUMENTS',
    'GENERIC_CALENDAR',
    'PERSON',
    'PROFESSIONAL_PRODUCT',
    'HOBBIES_PROJECT',
    'PERSON_PERSONAL',
    'DESTINATION',
];

export default {
    name: 'PotentialPagesSection',
    components: {
        PotentialPagesList,
    },
    mixins: [
    ],
    props: {
        existingPagesTemplateRefs: {
            type: Array,
            default: () => [],
        },
    },
    emits: [
        'openPageCreation',
    ],
    data() {
        return {

        };
    },
    computed: {
        allAvailablePages() {
            return availablePages();
        },
        unusedAvailablePages() {
            return this.allAvailablePages.filter((potentialPage) => {
                // Check if any of the template refs in the potential pages are already used in existing pages
                const isUsed = this.isPageInArray(potentialPage, this.existingPagesTemplateRefs);
                return !isUsed;
            });
        },
        topPotentialPages() {
            return this.unusedAvailablePages.filter((potentialPage) => {
                // Return top page objects from unused available pages arr
                const inTopList = this.isPageInArray(potentialPage, topPotentialPagesTemplateRefs);
                return inTopList;
            });
        },
        randomlySortedTopPages() {
            return _.shuffle(this.topPotentialPages);
        },
        remainingPotentialPages() {
            return this.unusedAvailablePages.filter((potentialPage) => {
                // Remove pages included in top list
                const inTopList = this.isPageInArray(potentialPage, topPotentialPagesTemplateRefs);
                return !inTopList;
            });
        },
        randomlySortedRemainingPages() {
            return _.shuffle(this.remainingPotentialPages);
        },
        fullPotentialPagesList() {
            return this.randomlySortedTopPages.concat(this.randomlySortedRemainingPages);
        },
        potentialPages() {
            return this.fullPotentialPagesList.slice(0, 7);
        },
    },
    methods: {
        isPageInArray(potentialPage, templateRefsArr) {
            const potentialPageTemplateRefs = potentialPage.templateRefs;
            return potentialPageTemplateRefs.some((templateRef) => {
                return templateRefsArr.includes(templateRef);
            });
        },
    },
    created() {
    },
};
</script>

<style scoped>
/* .o-potential-pages-section {
} */
</style>
