<template>
    <div class="c-error-404">
        <ErrorTemplate
            v-bind="$attrs"
            image="ThinkingBird_72dpi.png"
            imageAlt="Marty, the Hylark bird, cannot find your page"
        >
            <template
                #status
            >
                404
            </template>

            <template
                #explanation
            >
                {{ $t('errors.404.explanation') }}
            </template>

            <template
                #message
            >
                {{ $t('errors.404.message') }}
            </template>
        </ErrorTemplate>
    </div>
</template>

<script>

import interactsWithErrorPages from '@/vue-mixins/interactsWithErrorPages.js';

export default {
    name: 'Error404',
    components: {

    },
    mixins: [
        interactsWithErrorPages,
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

/*.c-error-404 {

} */

</style>
