<template>
    <div class="o-entity-timekeeper">
        <TimekeeperIndividual
            :item="item"
        >
        </TimekeeperIndividual>
    </div>
</template>

<script>

import TimekeeperIndividual from '@/components/timekeeper/TimekeeperIndividual.vue';

export default {
    name: 'EntityTimekeeper',
    components: {
        TimekeeperIndividual,
    },
    mixins: [
    ],
    props: {
        item: {
            type: Object,
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

/*.o-entity-timekeeper {

} */

</style>
