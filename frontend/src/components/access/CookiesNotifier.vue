<template>
    <Teleport
        :to="$root.$el"
    >
        <CookiePanel
            v-if="showCookiesPanel"
            :closable="true"
            @closeCookiesPanel="closeCookiesPanel"
        >
        </CookiePanel>
    </Teleport>
    <PopupBasic
        class="o-cookies-notifier"
        v-bind="$attrs"
        containerClass="p-1 text-center bg-sky-100 z-over"
    >

        <div class="flex items-center">
            <p class="text-xxs">
                Allow functional cookies to enable this feature
            </p>
            <button
                class="button--xs bg-sky-200 hover:bg-sky-300 transition-2eio text-sky-600 ml-2"
                type="button"
                @click="openCookiesPanel"
            >
                Cookies settings
            </button>
        </div>
    </PopupBasic>
</template>

<script>

import CookiePanel from '@/components/access/CookiePanel.vue';

export default {
    name: 'CookiesNotifier',
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

/*.o-cookies-notifier {

} */

</style>
