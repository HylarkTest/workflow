<template>
    <Modal
        class="o-refine-matches-modal"
        containerClass="p-8 w-600p"
        @closeModal="closeModal"
    >
        <section
            class="mb-10"
        >
            <h2
                class="text-center font-bold text-2xl mb-8"
            >
                {{ $tc('registration.confirm.refine.title', matchesLength, { recordType: firstRecordName }) }}
            </h2>

            <div>
                <div
                    v-for="(match, index) in formattedMatches"
                    :key="match.mergeVal"
                    class="p-4 rounded-lg my-4 bg-cm-100 relative"
                >
                    <RefineMatch
                        :match="match"
                        @mergeOptions="mergeOptions"
                    >

                    </RefineMatch>

                    <div
                        v-if="hasMultipleMatches"
                        class="o-refine-matches-modal__count circle-center"
                        :class="bgColorClass(match.color)"
                    >
                        {{ index + 1 }} / {{ matchesLength }}
                    </div>
                </div>
            </div>
        </section>

        <WhenMerge>
        </WhenMerge>
    </Modal>
</template>

<script>

import RefineMatch from '@/components/customize/RefineMatch.vue';
import WhenMerge from '@/components/customize/WhenMerge.vue';

import { mergeBlueprints } from '@/core/mappings/templates/helpers.js';

export default {
    name: 'RefineMatchesModal',
    components: {
        RefineMatch,
        WhenMerge,
    },
    mixins: [
    ],
    props: {
        matches: {
            type: Array,
            required: true,
        },
        samePairs: {
            type: [null, Object],
            required: true,
        },
    },
    emits: [
        'updateNewPages',
        'closeModal',
    ],
    data() {
        return {

        };
    },
    computed: {
        filteredMatches() {
            return this.matches.filter((match) => {
                return this.samePairs?.[match.mergeVal];
            });
        },
        formattedMatches() {
            return this.filteredMatches.map((match) => {
                const formattedVal = _.camelCase(match.mergeVal);
                return {
                    ...match,
                    formattedVal,
                    name: this.$t(`registration.common.mergeTypes.${formattedVal}`),
                };
            });
        },
        hasMultipleMatches() {
            return this.matchesLength > 1;
        },
        matchesLength() {
            return this.filteredMatches.length;
        },
        firstMatch() {
            return this.formattedMatches[0];
        },
        firstRecordName() {
            return `"${this.firstMatch?.name}"`;
        },
    },
    methods: {
        bgColorClass(color) {
            return `bg-${color}-600`;
        },
        mergeOptions({ selectedOptions, mergeVal }) {
            const newPages = mergeBlueprints(selectedOptions, mergeVal);
            this.$emit('updateNewPages', newPages);
        },
        closeModal() {
            this.$emit('closeModal');
        },
    },
    watch: {
        filteredMatches(newVal) {
            if (!newVal.length) {
                this.closeModal();
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-refine-matches-modal {
    &__count {
        @apply
            absolute
            font-bold
            h-6
            -left-3
            text-cm-00
            text-sm
            -top-3
            w-12
        ;
    }
}

</style>
