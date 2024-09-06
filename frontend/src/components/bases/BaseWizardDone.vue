<template>
    <div class="o-base-wizard-done">
        <h1 class="o-creation-wizard__header--sm">
            Your new base has been created!
        </h1>

        <h2 class="o-creation-wizard__prompt mt-4">
            What do you want to do next?
        </h2>

        <div class="flex flex-col items-center">
            <RouterLink
                :to="{ name: 'home', params: { baseId: base.id } }"
                class="button--lg my-2 bg-cm-100 w-full text-center max-w-sm hover:shadow-lg"
                type="button"
            >
                <i
                    class="far fa-fw fa-home mr-2 text-primary-500"
                >
                </i>

                Take me to "{{ baseName }}" home
            </RouterLink>

            <RouterLink
                :to="{ name: 'settings.general', params: { baseId: base.id } }"
                class="button--lg my-2 bg-cm-100 w-full text-center max-w-sm hover:shadow-lg"
                type="button"
            >
                <i
                    class="far fa-fw fa-cog mr-2 text-primary-500"
                >
                </i>

                Take me to "{{ baseName }}" settings
            </RouterLink>
        </div>
    </div>
</template>

<script>

export default {
    name: 'BaseWizardDone',
    components: {

    },
    mixins: [
    ],
    props: {
        base: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        baseName() {
            return this.base.name;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.o-base-wizard-done {

} */

</style>
