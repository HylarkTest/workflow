<template>
    <div class="o-refine-match">
        <p class="font-semibold mb-2">
            {{ numberWritten }} of your pages display
            <span :class="textColorClass">"{{ mergeName }}"</span>
        </p>

        <div class="text-smbase text-cm-600">
            <div
                v-for="page in pages"
                :key="page.id"
            >
                <i
                    class="far fa-fw mr-1"
                    :class="page.symbol"
                >

                </i>

                {{ page.pageName || page.name }}
            </div>
        </div>

        <div class="border-t border-solid border-cm-300 pt-4 mt-4">
            <template
                v-if="!canSeeExtendedOptions"
            >
                <p class="text-center text-smbase">
                    If you want these pages to share information,
                    you can merge the record types displayed on
                    <strong>{{ joined }}</strong> together.
                </p>
                <div
                    class="centered mt-2"
                >
                    <button
                        class="button--sm button-secondary"
                        type="button"
                        @click="goMerge"
                    >
                        Merge
                    </button>
                </div>
            </template>

            <template
                v-else
            >
                <p
                    class="text-center text-smbase mb-2"
                >
                    Select the record types you wish to merge
                </p>
                <div class="flex gap-2 flex-wrap">
                    <div
                        v-for="page in pages"
                        :key="page.id"
                        class="bg-secondary-200 py-1 px-3 rounded-md"
                    >
                        <CheckHolder
                            v-model="selectedPages"
                            :val="page"
                            predicate="id"
                        >
                            <span
                                class="text-sm"
                            >
                                <i
                                    class="far fa-fw mr-1"
                                    :class="page.symbol"
                                >

                                </i>

                                {{ page.pageName || page.name }}
                            </span>
                        </CheckHolder>
                    </div>
                </div>
                <div
                    class="centered mt-2"
                >
                    <button
                        class="button--sm button-secondary"
                        :class="{ unclickable: isSelectedBlocked }"
                        type="button"
                        :disabled="isSelectedBlocked"
                        @click="mergeOptions(selectedPages)"
                    >
                        Merge selected
                    </button>

                    <button
                        class="button--sm button-gray ml-2"
                        type="button"
                        @click="cancelMerge"
                    >
                        {{ $t('common.cancel') }}
                    </button>
                </div>
            </template>
        </div>

    </div>
</template>

<script>

export default {
    name: 'RefineMatch',
    components: {

    },
    mixins: [
    ],
    props: {
        match: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'mergeOptions',
    ],
    data() {
        return {
            canSeeExtendedOptions: false,
            selectedPages: [],
        };
    },
    computed: {
        mergeName() {
            return this.match.name;
        },
        pages() {
            return this.match.pages;
        },
        pagesLength() {
            return this.pages.length;
        },
        numberWritten() {
            return this.$t(`common.numbers.capitalized.${this.pagesLength}`);
        },
        textColorClass() {
            return `text-${this.match.color}-600`;
        },
        pageNames() {
            return this.pages.map((page) => {
                const name = page.pageName || page.name;
                return `"${name}"`;
            });
        },
        joined() {
            let names = this.pageNames;
            const conjunction = 'and';
            const namesLength = names.length;
            if (namesLength < 2) {
                return names[0];
            }
            if (namesLength < 3) {
                return names.join(` ${conjunction} `);
            }
            names = names.slice();
            names[namesLength - 1] = `${conjunction} ${names[namesLength - 1]}`;
            return names.join(', ');
        },
        selectedLength() {
            return this.selectedPages.length;
        },
        isSelectedBlocked() {
            return this.selectedLength < 2;
        },

    },
    methods: {
        goMerge() {
            if (this.pagesLength > 2) {
                this.canSeeExtendedOptions = true;
            } else {
                this.mergeOptions();
            }
        },
        mergeOptions(options = null) {
            const selectedOptions = options || this.match.pages;
            this.$emit('mergeOptions', { selectedOptions, mergeVal: this.match.mergeVal });
        },
        cancelMerge() {
            this.canSeeExtendedOptions = false;
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-refine-match {

} */

</style>
