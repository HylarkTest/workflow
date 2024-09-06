<template>
    <CustomizeFoundation class="o-data-import">
        <template #header>
            Import data
        </template>

        <ImportProcess>
        </ImportProcess>
    </CustomizeFoundation>
</template>

<script>

import ImportProcess from './ImportProcess.vue';
import CustomizeFoundation from '@/components/customize/CustomizeFoundation.vue';

export default {
    name: 'DataImport',
    components: {
        CustomizeFoundation,
        ImportProcess,
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

/*.o-data-import {

} */

</style>
