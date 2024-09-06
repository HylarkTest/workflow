<template>
    <div class="c-error-custom">
        <ErrorTemplate
            v-bind="$attrs"
        >
            <template
                v-if="status"
                #status
            >
                {{ status }}
            </template>

            <template
                #message
            >
                {{ customMessage }}
            </template>
        </ErrorTemplate>
    </div>
</template>

<script>

import interactsWithErrorPages from '@/vue-mixins/interactsWithErrorPages.js';

export default {
    name: 'ErrorCustom',
    components: {

    },
    mixins: [
        interactsWithErrorPages,
    ],
    props: {
        customMessage: {
            type: String,
            required: true,
        },
        status: {
            type: String,
            default: null,
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

/*.c-error-custom {

} */

</style>
