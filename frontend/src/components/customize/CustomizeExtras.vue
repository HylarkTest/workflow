<template>
    <CustomizeFoundation class="o-customize-extras">

        <template #header>
            Extras
        </template>

        <ExtrasWidget class="customize__container p-6">

        </ExtrasWidget>
    </CustomizeFoundation>
</template>

<script>

import CustomizeFoundation from './CustomizeFoundation.vue';
import ExtrasWidget from './ExtrasWidget.vue';

export default {
    name: 'CustomizeExtras',
    components: {
        CustomizeFoundation,
        ExtrasWidget,
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

/*.o-customize-extras {

} */

</style>
