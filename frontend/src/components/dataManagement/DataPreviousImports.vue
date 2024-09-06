<template>
    <CustomizeFoundation class="o-data-previous-imports">
        <template #header>
            Previous and active imports
        </template>

        <PreviousImports>
        </PreviousImports>
    </CustomizeFoundation>
</template>

<script>

import PreviousImports from './PreviousImports.vue';
import CustomizeFoundation from '@/components/customize/CustomizeFoundation.vue';

export default {
    name: 'DataPreviousImports',
    components: {
        CustomizeFoundation,
        PreviousImports,
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

/*.o-data-previous-imports {

} */

</style>
