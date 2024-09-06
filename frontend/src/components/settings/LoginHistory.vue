<template>
    <div class="o-login-history">
        <div
            v-for="item in loginHistory"
            :key="item.id"
            class="o-login-history__item"
        >
            <div
                class="o-login-history__created"
            >
                {{ $dayjs(item.createdAt).format('LLL') }}
            </div>

            <div class="o-login-history__ip">
                <label
                    class="header-uppercase-light mr-1"
                >
                    IP address:
                </label>
                <div class="text-cm-600">
                    {{ item.ip }}
                </div>
            </div>

            <div class="o-login-history__browser">
                {{ item.browser }}
            </div>

            <div class="o-login-history__device">
                <p>
                    {{ item.device }} {{ '&nbsp;-&nbsp;' }}
                </p>

                <p>
                    {{ item.platform }} {{ '&nbsp;-&nbsp;' }}
                </p>

                <p
                    v-t="'devices.types.' + item.deviceType"
                >
                </p>

            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: 'LoginHistory',
    components: {

    },
    mixins: [
    ],
    props: {
        loginHistory: {
            type: Array,
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

.o-login-history {
    @apply
        text-sm
    ;

    &__item {
        @apply
            flex
            flex-col
            flex-wrap
            justify-between
            px-4
            py-2
            rounded-lg
            w-full
        ;

        &:nth-child(odd) {
            @apply
                bg-cm-100
            ;
        }

        @media (min-width: 1024px) {
            & {
                @apply
                    flex-row
                ;
            }
        }
    }

    &__created {
        @apply
            font-semibold
            mb-1
            w-56
        ;
    }

    &__ip {
        @apply
            flex
            items-baseline
            w-44
        ;
    }

    &__browser {
        @apply
            w-36
        ;
    }

    &__device {
        @apply
            flex
            flex-wrap
        ;
    }
}

</style>
