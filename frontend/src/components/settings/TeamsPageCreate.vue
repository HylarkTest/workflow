<template>
    <div class="o-teams-page-create">
        <PageCreate
            :teamId="teamId"
        >
        </PageCreate>
    </div>
</template>

<script>

import PageCreate from '@/components/settings/PageCreate.vue';

export default {

    name: 'TeamsPageCreate',
    components: {
        PageCreate,
    },
    mixins: [
    ],
    props: {
        teamId: {
            type: String,
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
<!--
<style scoped>
.o-teams-page-create {

}
</style> -->
