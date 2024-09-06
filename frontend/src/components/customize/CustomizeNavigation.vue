<template>
    <CustomizeFoundation
        class="o-customize-navigation"
    >
        <template #header>
            Navigation
        </template>

        <NavigationLinks
            :allPages="allPages"
        >
        </NavigationLinks>

    </CustomizeFoundation>
</template>

<script>

import CustomizeFoundation from './CustomizeFoundation.vue';
import NavigationLinks from './NavigationLinks.vue';

export default {
    name: 'CustomizeNavigation',
    components: {
        CustomizeFoundation,
        NavigationLinks,
    },
    mixins: [
    ],
    props: {
        spaces: {
            type: Array,
            required: true,
        },
        allPages: {
            type: Array,
            required: true,
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

/*.o-customize-navigation {

} */

</style>
