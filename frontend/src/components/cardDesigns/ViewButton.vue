<template>
    <div
        v-blur="closePopup"
        class="c-view-button button-primary rounded-md flex items-center"
    >
        <div
            ref="button"
            class="centered"
        >
            <button
                v-t="'common.view'"
                class="button--xs button-primary"
                type="button"
                @click.exact="openViewModal"
                @click.alt.prevent="goToPage"
                @click.shift.prevent="openFullEditModal"
            >
            </button>

            <i class="fal fa-pipe text-primary-400">
            </i>

            <button
                class="button--xs button-primary"
                type="button"
                @click.stop="togglePopup"
            >
                <i
                    class="far mt-0.5 block"
                    :class="popupOpen ? 'fa-angle-up' : 'fa-angle-down'"
                >
                </i>
            </button>
        </div>

        <PopupBasic
            v-if="popupOpen"
            ref="options"
            :top="true"
            :activator="$refs.button"
        >
            <div
                class="p-2 text-xssm"
            >
                <ButtonEl
                    class="c-view-button__option mb-1 button--xs bg-cm-100 hover:bg-cm-200"
                    @click="openViewModal"
                >
                    Dialog
                    <span class="c-view-button__key">Click "View"</span>
                </ButtonEl>

                <RouterLink
                    :to="itemRoute"
                    class="c-view-button__option button--xs bg-cm-100 hover:bg-cm-200 mb-1"
                >
                    Page
                    <span class="c-view-button__key">Click "View" + ALT / OPTION</span>
                </RouterLink>

                <ButtonEl
                    class="c-view-button__option button--xs bg-cm-100 hover:bg-cm-200"
                    @click="openFullEditModal"
                >
                    Full edit
                    <span class="c-view-button__key">Click "View" + SHIFT</span>
                </ButtonEl>

                <!-- <button
                    class="c-view-button__option"
                    type="button"
                >
                    New tab
                    <span>CLICK + CONTROL / CLICK + COMMAND</span>
                </button> -->
            </div>
        </PopupBasic>

        <!-- <Modal
            v-if="isModalOpen"
            containerClass="w-full lg:w-10/12"
            :containerStyle="{ height: '80vh' }"
            @closeModal="closeModal"
        >
            <FullView
                :item="item"
                :page="page"
                @closeModal="closeModal"
            >
            </FullView>
        </Modal> -->

        <EntityEditModal
            v-if="showFullEdit"
            :item="item"
            :page="page"
            @closeModal="closeFullEdit"
        >
        </EntityEditModal>
    </div>
</template>

<script>

import interactsWithViewsItem from '@/vue-mixins/views/interactsWithViewsItem.js';

export default {
    name: 'ViewButton',
    components: {
    },
    mixins: [
        interactsWithViewsItem,
    ],
    props: {

    },
    data() {
        return {
            popupOpen: false,
        };
    },
    computed: {

    },
    methods: {
        togglePopup() {
            this.popupOpen = !this.popupOpen;
        },
        closePopup() {
            this.popupOpen = false;
        },
        goToPage() {
            this.$router.push(this.itemRoute);
        },
        openViewModal() {
            this.closePopup();
            this.openFullView();
        },
        openFullEditModal() {
            this.openFullEdit();
            this.closePopup();
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-view-button {
    &__option {
        @apply
            block
            font-semibold
            text-center
            w-full
        ;
    }

    &__key {
        @apply
            block
            text-cm-500
            text-xxs
        ;
    }
}

</style>
