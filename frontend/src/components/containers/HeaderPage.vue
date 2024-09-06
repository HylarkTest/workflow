<template>
    <div class="c-header-page">

        <h1 class="header-1 mb-10">
            <slot
                name="header"
            >
            </slot>
        </h1>

        <slot>
        </slot>
    </div>
</template>

<script>

export default {
    name: 'HeaderPage',
    components: {

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

/*
.c-header-page {

}
 */

</style>
