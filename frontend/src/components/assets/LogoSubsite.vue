<template>
    <div class="c-logo-subsite flex items-end">
        <img
            :src="'/images/logos/logo_full_dm.svg'"
            class="c-logo-subsite__logo"
        />
        <p class="ml-1 font-semibold text-cm-300">
            {{ subsiteName }}
        </p>
    </div>
</template>

<script>

export default {
    name: 'LogoSubsite',
    components: {

    },
    mixins: [
    ],
    props: {
        subsiteName: {
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

<style scoped>

.c-logo-subsite {
    &__logo {
        width:  130px;
    }
}

</style>
