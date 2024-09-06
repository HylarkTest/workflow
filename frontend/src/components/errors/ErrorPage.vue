<template>
    <div class="c-error-page">
        <component
            :is="errorComponent"
            :customMessage="customMessage"
            :isAuthenticated="isAuthenticated"
            :customLink="customErrorButtonLink"
            :customLinkTextPath="customErrorButtonTextPath"
            :status="statusCode"
        >
        </component>
    </div>
</template>

<script>

import ErrorCustom from './ErrorCustom.vue';
import Error404 from './Error404.vue';
import ErrorOther from './ErrorOther.vue';

const commonStatuses = ['404'];

export default {
    name: 'ErrorPage',
    components: {
        ErrorCustom,
        Error404,
        ErrorOther,
    },
    props: {
        status: {
            type: String,
            default: null,
        },
        errorButtonUrl: {
            type: String,
            default: '',
        },
        errorButtonTextPath: {
            type: String,
            default: '',
        },
    },
    data() {
        return {
        };
    },
    computed: {
        errorComponent() {
            if (this.customMessage) {
                return 'ErrorCustom';
            }
            if (this.isCommonStatus) {
                return `Error${this.statusCode}`;
            }
            return 'ErrorOther';
        },

        statusCode() {
            return this.status || this.query.status;
        },
        isCommonStatus() {
            return commonStatuses.includes(this.statusCode);
        },

        customMessage() {
            return this.query.message;
        },

        isAuthenticated() {
            return this.$root.isAuthenticated;
        },

        query() {
            return this.$route.query;
        },

        customErrorButtonLink() {
            if (this.query.link) {
                return this.query.link;
            }
            if (this.errorButtonUrl) {
                return this.errorButtonUrl;
            }
            return '';
        },
        customErrorButtonTextPath() {
            if (this.query.linkText) {
                return this.query.linkText;
            }
            if (this.errorButtonTextPath) {
                return this.errorButtonTextPath;
            }
            return '';
        },
    },
    methods: {},
};

</script>

<style scoped>
.c-error-page {
    @apply
        flex
        flex-col
        h-screen
        items-center
        justify-center
        px-2
        w-full
    ;

    &__status {
        @apply
            text-9xl
        ;
    }

    &__message {
        @apply
            text-lg
        ;
    }
}
</style>
