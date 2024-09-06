<template>
    <SupportSearch
        class="o-support-modal"
        containerWidthClass="w-4/5"
        :areArticlesInline="true"
        :presetSearchTerm="contentQuery"
        v-bind="$attrs"
        @closeModal="closeModal"
    >
        <template
            #header
        >
            Learn more about...
            <span
                class="text-primary-700"
            >
                {{ sectionTitle }}
            </span>
        </template>
    </SupportSearch>
</template>

<script>

import SupportSearch from '@/components/supportSite/SupportSearch.vue';

export default {
    name: 'SupportModal',
    components: {
        SupportSearch,
    },
    mixins: [
    ],
    props: {
        sectionTitle: {
            type: String,
            required: true,
        },
        contentQuery: {
            type: String,
            default: '',
        },
    },
    emits: [
        'closeModal',
    ],
    data() {
        return {

        };
    },
    computed: {
        // sectionTitle() {
        //     return this.supportPropsObj.sectionTitle;
        // },
    },
    methods: {
        closeModal() {
            this.$emit('closeModal');
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-support-modal {

} */

</style>
