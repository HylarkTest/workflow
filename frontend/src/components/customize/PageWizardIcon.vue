<template>
    <div class="o-page-wizard-icon">
        <div class="max-w-xl w-3/4">
            <h2 class="o-creation-wizard__prompt pt-20">
                Select an icon to represent {{ pageForm.name }}
            </h2>

            <div
                class="mb-4 flex items-center justify-center"
                :class="pageForm.symbol ? 'opacity-100' : 'opacity-0'"
            >
                <p class="mr-3 uppercase text-cm-400 font-semibold text-sm">
                    Your selection
                </p>
                <i
                    class="fal text-xl text-primary-600"
                    :class="pageForm.symbol || 'fa-question'"
                >
                </i>
            </div>

            <IconSearch
                :selectedIcon="pageForm.symbol"
                @update:selectedIcon="updateForm"
            >
            </IconSearch>
        </div>
    </div>
</template>

<script>

import IconSearch from '@/components/assets/IconSearch.vue';

export default {
    name: 'PageWizardIcon',
    components: {
        IconSearch,
    },
    mixins: [
    ],
    props: {
        pageForm: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:pageForm',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        updateForm(symbol) {
            if (symbol) {
                this.$emit('update:pageForm', { valKey: 'symbol', newVal: symbol, nextStep: 'NEXT' });
            } else {
                this.$emit('update:pageForm', { valKey: 'symbol', newVal: null });
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-page-wizard-icon {
}*/

</style>
