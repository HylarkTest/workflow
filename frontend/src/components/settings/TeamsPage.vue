<template>
    <div class="o-teams-page">
        <PageMain
            :pageId="pageId"
            context="teams"
        >
        </PageMain>
    </div>
</template>

<script>

import PageMain from '@/components/settings/PageMain.vue';

export default {

    name: 'TeamsPage',
    components: {
        PageMain,
    },
    mixins: [
    ],
    props: {
        pageId: {
            type: String,
            default: '',
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

<!-- <style scoped>
.o-teams-page {

}
</style> -->
