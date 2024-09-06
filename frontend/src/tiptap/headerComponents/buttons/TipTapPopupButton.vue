<template>
    <div
        v-blur="closePopup"
        class="o-tip-tap-popup-button"
    >
        <TipTapButton
            :ref="refName"
            v-bind="$attrs"
            @handleClick="togglePopup"
        >
            <template #button>
                <slot name="button">
                </slot>
            </template>

            <template #popup>
                <PopupBasic
                    v-if="isPopupOpen"
                    containerClass="p-1"
                    :activator="$refs[refName]"
                    :width="popupWidth"
                    @click="closePopup"
                >
                    <slot name="popup"></slot>
                </PopupBasic>
            </template>
        </TipTapButton>
    </div>
</template>

<script>
import TipTapButton from '@/tiptap/headerComponents/buttons/TipTapButton.vue';

export default {
    name: 'TipTapPopupButton',
    components: {
        TipTapButton,
    },
    mixins: [
    ],
    props: {
        refName: {
            type: String,
            required: true,
        },
        popupWidth: {
            type: Number,
            default: 128,
        },
    },
    data() {
        return {
            isPopupOpen: false,
        };
    },
    computed: {
    },
    methods: {
        closePopup() {
            this.isPopupOpen = false;
        },
        togglePopup() {
            this.isPopupOpen = !this.isPopupOpen;
        },
    },
    created() {
    },
};
</script>

<style scoped>
.o-tip-tap-popup-button {
    &__swatch {
        @apply
            border
            border-cm-main
            border-solid
            h-7
            hover:shadow
            m-0.5
            rounded-md
            w-7
        ;
    }
}
</style>
