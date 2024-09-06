<template>
    <div class="o-page-wizard-done">
        <div class="max-w-xl">
            <h1 class="o-creation-wizard__header--sm">
                Your new {{ featureName }} page has been created!
            </h1>

            <h2 class="o-creation-wizard__prompt mt-4">
                What do you want to do next?
            </h2>

            <div class="flex flex-col items-center">
                <RouterLink
                    :to="pageLink"
                    class="button--lg my-2 bg-cm-100 w-full text-center max-w-sm hover:shadow-lg"
                    type="button"
                >
                    <i
                        class="far fa-fw mr-2 text-primary-500"
                        :class="page.symbol"
                    >
                    </i>

                    Take me to "{{ page.name }}"
                </RouterLink>
                <!-- <button
                    class="button--lg my-2 bg-cm-100 w-full text-center max-w-sm hover:shadow-lg"
                    type="button"
                    @click="customizeFurther"
                >
                    <i
                        class="far fa-fw mr-2 fa-sliders-simple text-primary-500"
                    >
                    </i>

                    Customize "{{ page.name }}" further
                </button> -->
                <button
                    class="button--lg my-2 bg-cm-100 w-full text-center max-w-sm hover:shadow-lg"
                    type="button"
                    @click="$emit('addAnother')"
                >
                    <i
                        class="far fa-memo fa-fw mr-2 text-primary-500"
                    >
                    </i>

                    Add another page
                </button>
                <button
                    class="button--lg my-2 bg-cm-100 w-full text-center max-w-sm hover:shadow-lg"
                    type="button"
                    @click="$emit('closeFullDialog')"
                >
                    <i
                        class="far fa-angle-left fa-fw mr-2 text-primary-500"
                    >
                    </i>
                    Go back go my spaces
                </button>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: 'PageWizardDone',
    components: {

    },
    mixins: [
    ],
    props: {
        pageForm: {
            type: Object,
            required: true,
        },
        page: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'closeFullDialog',
        'addAnother',
        'customizeFurther',
    ],
    data() {
        return {

        };
    },
    computed: {
        pageLink() {
            const name = this.pageType === 'ENTITIES' || this.pageType === 'ENTITY'
                ? 'page'
                : 'feature';
            return { name, params: { pageId: this.page.id } };
        },
        featureName() {
            return this.$t(`common.pageTypes.${_.camelCase(this.pageType)}`);
        },
        pageType() {
            return this.page.type;
        },
    },
    methods: {
        customizeFurther() {
            this.$emit('closeFullDialog');
            this.$emit('customizeFurther', this.page);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-page-wizard-done {

} */

</style>
