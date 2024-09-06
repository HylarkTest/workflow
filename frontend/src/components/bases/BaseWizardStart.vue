<template>
    <div class="o-base-wizard-start">
        <h1 class="o-creation-wizard__header">
            Create a collaborative base!
        </h1>

        <h2 class="o-creation-wizard__prompt">
            Let's start with a few details
        </h2>

        <div>
            <div class="mb-10 flex flex-col items-center">
                <label class="o-creation-wizard__description mb-2">
                    What's your new base called?*
                </label>
                <InputBox
                    ref="input"
                    class="w-56"
                    :modelValue="base.name"
                    bgColor="gray"
                    :maxLength="maxBaseNameLength"
                    placeholder="Base name"
                    @update:modelValue="updateBase('name', $event)"
                >
                </InputBox>
            </div>

            <div class="flex flex-col items-center mb-10">
                <label class="o-creation-wizard__description mb-2">
                    Want to give it an image or logo?
                </label>

                <ImageContainer
                    editMode
                    :modelValue="base.image"
                    displaySize="h-56 w-56"
                    onlyCroppedImage
                    @update:modelValue="updateBase('image', $event)"
                >
                </ImageContainer>
            </div>

            <div class="bg-primary-100 mb-4 rounded-xl p-3 text-center">
                <p
                    class="text-sm text-cm-600 font-medium"
                >
                    Want to learn more about collaborative bases?
                </p>

                <button
                    class="button--sm button-primary--medium mt-2"
                    type="button"
                    @click="openModal"
                >
                    Check out the guide
                </button>
            </div>
        </div>

        <AssistModal
            v-if="isModalOpen"
            headerTextString="All about collaborative bases"
            @closeModal="closeModal"
        >
            <QuestionsAnswers
                :qaArr="qaArr"
            >
            </QuestionsAnswers>
        </AssistModal>
    </div>
</template>

<script>

import ImageContainer from '@/components/images/ImageContainer.vue';
import { maxBaseNameLength } from '@/core/data/bases.js';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

const qaArr = [1, 2, 3].map((number) => {
    return {
        questionPath: `bases.information.collaborativeBases.${number}.question`,
        answerPath: `bases.information.collaborativeBases.${number}.answer`,
    };
});

export default {
    name: 'BaseWizardStart',
    components: {
        ImageContainer,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        base: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:base',
    ],
    data() {
        return {

        };
    },
    computed: {
    },
    methods: {
        updateBase(valKey, val) {
            this.$proxyEvent(val, this.base, valKey, 'update:base');
        },
        focusName() {
            this.$refs.input.focus();
        },
    },
    created() {
        this.maxBaseNameLength = maxBaseNameLength;
        this.qaArr = qaArr;
    },
    mounted() {
        this.focusName();
    },
};
</script>

<style scoped>

/*.o-base-wizard-start {

} */

</style>
