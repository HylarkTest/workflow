<template>
    <div
        class="c-layout-page-simple nav-spacing--top nav-spacing--bottom"
        :class="navigationClass"
    >
        <slot>
        </slot>
    </div>
</template>

<script>

import GET_UI from '@/graphql/client/GetUI.gql';

export default {
    name: 'LayoutPageSimple',
    components: {

    },
    mixins: [
    ],
    props: {

    },
    apollo: {
        ui: {
            query: GET_UI,
            update(data) {
                return data.ui;
            },
            client: 'defaultClient',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        navigationClass() {
            return this.ui?.navExtensionClass;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-layout-page-simple {

} */

</style>
