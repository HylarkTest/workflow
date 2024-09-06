<template>
    <div class="o-expired-session p-6 max-w-lg">
        <div>
            <p class="text-center text-lg font-semibold mb-2">
                Your session has expired
            </p>

            <p class="text-center text-sm">
                Your session has expired due to inactivity. Please log in again to pick up where you left off!
            </p>

            <RouterLink
                :to="{ name: 'access.login' }"
                class="button button-primary"
            >
                Sign in
            </RouterLink>
        </div>

        <TopTip
            class="mt-3 bg-secondary-100"
        >
            Don't want to be logged out every few hours?
            If you are on a secure device and wish to be kept logged in
            for an extended period, check "Remember me".
        </TopTip>
    </div>
</template>

<script>

import TopTip from '@/components/display/TopTip.vue';

export default {
    name: 'ExpiredSession',
    components: {
        TopTip,
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

    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.o-expired-session {

} */

</style>
