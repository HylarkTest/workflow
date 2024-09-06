<template>
    <div class="o-personal-page-create">
        <PageCreate
            :spaceId="spaceId"
        >
        </PageCreate>
    </div>
</template>

<script>

import PageCreate from '@/components/settings/PageCreate.vue';

export default {

    name: 'PersonalPageCreate',
    components: {
        PageCreate,
    },
    mixins: [
    ],
    props: {
        spaceId: {
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

<!-- <style scoped>
.o-personal-page-create {

}
</style> -->
