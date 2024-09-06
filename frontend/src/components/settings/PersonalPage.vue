<template>
    <div class="o-personal-page">
        <PageMain
            :pageId="pageId"
            context="personal"
        >
        </PageMain>
    </div>
</template>

<script>

import PageMain from '@/components/settings/PageMain.vue';

export default {

    name: 'PersonalPage',
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
.o-personal-page {

}
</style> -->
