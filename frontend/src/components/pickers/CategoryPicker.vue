<template>
    <component
        :is="dropdownComponent"
        class="c-category-picker w-full"
        :popupProps="{ maxHeightProp: '7.5rem' }"
        displayRule="name"
        :options="categories"
        placeholder="Select a category group"
        v-bind="$attrs"
    >

    </component>
</template>

<script>

import BASIC_CATEGORIES from '@/graphql/categories/queries/BasicCategories.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';

export default {
    name: 'CategoryPicker',
    components: {

    },
    mixins: [
    ],
    props: {
        dropdownComponent: {
            type: String,
            default: 'DropdownBox',
        },
    },
    apollo: {
        categories: {
            query: BASIC_CATEGORIES,
            update: (data) => initializeConnections(data).categories,
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {

        };
    },
    computed: {
    },
    methods: {

    },
    created() {
    },
};
</script>

<style scoped>

/*.c-category-picker {

} */

</style>
