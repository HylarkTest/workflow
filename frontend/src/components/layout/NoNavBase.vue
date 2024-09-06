<template>
    <div class="b-no-nav-base">
        <a
            class="b-no-nav-base__image z-cover"
            :href="landingUrl"
            @click.stop
        >
            <img
                class="b-no-nav-base__logo"
                :src="'/images/logos/40h_logo.svg'"
                alt="Hylark logo"
            />
        </a>
        <slot>
        </slot>
    </div>
</template>

<script>

import config from '@/core/config.js';

export default {
    name: 'NoNavBase',
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
        landingUrl() {
            return config('app.landing-url');
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.b-no-nav-base {
    @apply
        relative
    ;

    &__image {
        @apply
            absolute
            left-8
            top-4
        ;
    }

    &__logo {
        height: 30px;
    }
}

@media (min-width: 700px) {
    .b-no-nav-base {
        &__logo {
            height: 40px;
        }
    }
}

</style>
