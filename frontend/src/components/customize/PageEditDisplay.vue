<template>
    <div class="o-page-edit-display">
        <!-- <SettingsHeaderLine>
            <template #header>
                Tab design
            </template>
        </SettingsHeaderLine>

        <SettingsHeaderLine>
            <template #header>
                Tab order
            </template>
        </SettingsHeaderLine> -->

        <SettingsHeaderLine>
            <template #header>
                Main details display
            </template>

            <div class="bg-cm-100 rounded-xl p-4">
                <div class="flex justify-end mb-1">
                    <button
                        v-t="'common.edit'"
                        class="button button-primary"
                        type="button"
                        @click="openModal"
                    >
                    </button>
                </div>

                <p class="header-uppercase mb-1">
                    Preview
                </p>

                <div class="bg-cm-00 rounded-xl p-8">
                    <FullInfo
                        :mapping="mapping"
                        :preview="true"
                        :page="page"
                    >
                    </FullInfo>
                </div>
            </div>

        </SettingsHeaderLine>

        <!-- <SettingsHeaderLine>
            <template #header>
                Relationships display
            </template>
        </SettingsHeaderLine> -->

        <DisplayEditModal
            v-if="isModalOpen"
            :mapping="mapping"
            :page="page"
            @closeModal="closeModal"
        >
        </DisplayEditModal>
    </div>
</template>

<script>

import DisplayEditModal from './DisplayEditModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

export default {
    name: 'PageEditDisplay',
    components: {
        DisplayEditModal,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        mapping: {
            type: Object,
            required: true,
        },
        page: {
            type: Object,
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

/*.o-page-edit-display {

} */

</style>
