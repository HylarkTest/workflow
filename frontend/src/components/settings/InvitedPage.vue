<template>
    <div class="o-invited-page">
        <PageMain
            :pageId="pageId"
            context="invited"
        >
        </PageMain>
    </div>
</template>

<script>

import PageMain from '@/components/settings/PageMain.vue';

export default {

    name: 'InvitedPage',
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
.o-invited-page {

}
</style> -->
