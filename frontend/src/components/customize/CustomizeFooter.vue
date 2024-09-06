<template>
    <CustomizeFoundation
        class="o-customize-footer"
    >
        <template #header>
            Footer
        </template>

        <FooterActions>
        </FooterActions>

    </CustomizeFoundation>
</template>

<script>

import FooterActions from './FooterActions.vue';
import CustomizeFoundation from './CustomizeFoundation.vue';

export default {
    name: 'CustomizeFooter',
    components: {
        FooterActions,
        CustomizeFoundation,
    },
    mixins: [
    ],
    props: {

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

/*.o-customize-footer {

} */

</style>
