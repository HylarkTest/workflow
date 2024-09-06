<template>
    <div class="c-error-other">
        <ErrorTemplate
            v-bind="$attrs"
        >
            <template
                #status
            >
                {{ status }}
            </template>

            <template
                #explanation
            >
                {{ $t('errors.other.explanation') }}
            </template>

            <template
                #message
            >
                {{ $t('errors.other.message') }}
            </template>
        </ErrorTemplate>
    </div>
</template>

<script>

import interactsWithErrorPages from '@/vue-mixins/interactsWithErrorPages.js';

export default {
    name: 'ErrorOther',
    components: {

    },
    mixins: [
        interactsWithErrorPages,
    ],
    props: {
        status: {
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

/*.c-error-other {

} */

</style>
