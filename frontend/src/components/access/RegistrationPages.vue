<template>
    <RegistrationBase
        class="o-registration-pages"
        :showNext="false"
        @nextStep="$emit('nextStep', 'pages')"
    >
    </RegistrationBase>
</template>

<script>

export default {
    name: 'RegistrationPages',
    components: {

    },
    mixins: [
    ],
    props: {

    },
    emits: [
        'nextStep',
    ],
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
.o-registration-pages {

}
*/

</style>
