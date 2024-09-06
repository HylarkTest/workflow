<template>
    <Modal
        class="o-support-options"
        containerClass="py-8 px-12"
        v-bind="$attrs"
    >

        <!-- <div class="flex justify-center mb-4">
            <BirdImage
                class="h-24"
                whichBird="Using Hylark Bird_72dpi.png"
            >
            </BirdImage>
        </div> -->

        <h1 class="text-center text-2xl font-bold text-primary-700 mb-4">
            How can we help?
        </h1>

        <div class="flex flex-col gap-3">
            <router-link
                :to="{ name: 'support' }"
                class="o-support-options__button shadow-primary-400/20"
            >
                <i class="fa-regular fa-book-open-cover fa-fw mr-3">
                </i>

                <span
                    class="text-sm text-cm-800"
                >
                    Knowledge base
                </span>
            </router-link>
            <ButtonEl
                class="o-support-options__button shadow-primary-400/20"
                @click="openModal"
            >
                <i class="fa-regular fa-message-pen fa-fw mr-3">
                </i>

                <span
                    class="text-sm text-cm-800"
                >
                    Contact us
                </span>
            </ButtonEl>
        </div>

        <div class="flex items-center mt-8 flex-col">
            <div class="rounded-full h-12 w-12 bg-primary-100 centered mb-2">
                <i class="far fa-envelope text-primary-600 text-lg">
                </i>
            </div>
            <p class="text-xs text-cm-500">
                Our mailbox is always open!
            </p>
            <a
                class="hover:underline text-xssm font-bold text-primary-800"
                href="mailTo:hello@hylark.com"
            >
                hello@hylark.com
            </a>
        </div>

        <SupportContactModal
            v-if="isModalOpen"
            @closeModal="closeModal"
            @closeFormModal="closeModal"
        >
        </SupportContactModal>
    </Modal>
</template>

<script>

import SupportContactModal from './SupportContactModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

export default {
    name: 'SupportOptions',
    components: {
        SupportContactModal,
    },
    mixins: [
        interactsWithModal,
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

.o-support-options {
    &__button {
        transition: 0.2s ease-in-out;

        @apply
            bg-primary-100
            border
            border-primary-500
            border-solid
            flex
            font-semibold
            items-center
            px-4
            py-3
            rounded-md
            text-primary-600
        ;

        &:hover {
            @apply
                border-primary-400
                shadow-md
                text-primary-500
            ;
        }
    }
}

</style>
