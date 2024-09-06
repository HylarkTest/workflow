<template>
    <div class="c-shortcuts">
        <RoundedIcon
            icon="fa-bolt-lightning"
            title="Shortcuts"
            @click="openShortcuts"
        >
        </RoundedIcon>

        <ShortcutsModal
            v-if="isModalOpen"
            :page="page"
            @goToShortcut="goToShortcut"
            @closeModal="closeModal"
        >
        </ShortcutsModal>
    </div>
</template>

<script>

import RoundedIcon from '@/components/buttons/RoundedIcon.vue';
import ShortcutsModal from '@/components/assets/ShortcutsModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

export default {
    name: 'Shortcuts',
    components: {
        RoundedIcon,
        ShortcutsModal,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        page: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'goToShortcut',
        'closeModal',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        openShortcuts() {
            this.openModal();
        },
        goToShortcut(shortcut) {
            this.closeModal();
            this.$emit('goToShortcut', shortcut);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-shortcuts {

} */

</style>
