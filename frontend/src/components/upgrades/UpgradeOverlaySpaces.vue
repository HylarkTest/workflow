<template>
    <UpgradeMessage
        class="c-upgrade-overlay-spaces"
        :info="info"
        :isOverlay="true"
    >
    </UpgradeMessage>
</template>

<script>

import UpgradeMessage from '@/components/upgrades/UpgradeMessage.vue';

const info = {
    title: 'upgrade.prompts.spaces.title',
    subtitle: {
        path: 'upgrade.prompts.spaces.subtitle',
        args: { currentNumber: '2', nextNumber: '20' },
    },
    tip: 'tips.spaces.focus',
};

export default {
    name: 'UpgradeOverlaySpaces',
    components: {
        UpgradeMessage,
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
        this.info = info;
    },
};
</script>

<style scoped>

/*.c-upgrade-overlay-spaces {

} */

</style>
