<template>
    <div class="o-preferences-cookies">
        <p>
            Cookie permissions are set per browser. Edit your cookie settings for this browser here.
        </p>

        <button
            class="button button-primary mt-2"
            type="button"
            @click="openCookiesPanel"
        >
            Edit cookies settings
        </button>

        <CookiePanel
            v-if="showCookiesPanel"
            :closable="true"
            @closeCookiesPanel="closeCookiesPanel"
        >
        </CookiePanel>
    </div>
</template>

<script>

import CookiePanel from '@/components/access/CookiePanel.vue';

export default {
    name: 'PreferencesCookies',
    components: {
        CookiePanel,
    },
    mixins: [
    ],
    props: {

    },
    data() {
        return {
            showCookiesPanel: false,
        };
    },
    computed: {

    },
    methods: {
        openCookiesPanel() {
            this.showCookiesPanel = true;
        },
        closeCookiesPanel() {
            this.showCookiesPanel = false;
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-preferences-cookies {

} */

</style>
