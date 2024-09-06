<template>
    <div class="c-error-403">
        <ErrorTemplate
            v-bind="$attrs"
            image="LostAndConfusedBird_72dpi.png"
            imageAlt="This action is not allowed"
        >
            <template
                #status
            >
                403
            </template>

            <template
                #explanation
            >
                {{ $t('errors.403.explanation') }}
            </template>

            <template
                #message
            >
                {{ $t('errors.403.message') }}
            </template>
        </ErrorTemplate>
    </div>
</template>

<script>

import interactsWithErrorPages from '@/vue-mixins/interactsWithErrorPages.js';

export default {
    name: 'Error403',
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

/*.c-error-403 {

} */

</style>
