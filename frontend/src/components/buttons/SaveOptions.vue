<template>
    <div class="c-save-options relative">
        <SaveButton
            v-for="button in buttons"
            :key="button"
            colorClass="button-primary--border"
            class="centered ml-2"
            buttonType="button"
            :class="disabledClass"
            :disabled="disabled"
            :titlePath="buttonsReference[button].textPath"
            @save="$emit('submit', button)"
        >
            <i
                :class="buttonsReference[button].icon"
            >
            </i>
        </SaveButton>
        <SaveButton
            :class="[{ 'ml-2': buttons.length }, disabledClass]"
            :disabled="disabled"
            :error="error"
            @save="$emit('submit', 'close')"
        >
        </SaveButton>
    </div>
</template>

<script>

const buttonsReference = {
    another: {
        icon: 'far fa-plus-circle',
        textPath: 'common.saves.saveAndAnother',
    },
    open: {
        icon: 'far fa-expand',
        textPath: 'common.saves.saveAndOpen',
    },
    // clone: {
    //     icon: 'fal fa-clone',
    //     textPath: 'common.saves.saveAndClone',
    // },
};

export default {
    name: 'SaveOptions',
    components: {
    },
    mixins: [
    ],
    props: {
        buttons: {
            type: Array,
            default: () => {
                return [];
            },
        },
        disabled: Boolean,
        error: {
            type: String,
            default: '',
        },
    },
    emits: [
        'submit',
    ],
    data() {
        return {

        };
    },
    computed: {
        disabledClass() {
            return { unclickable: this.disabled };
        },
    },
    methods: {

    },
    created() {
        this.buttonsReference = buttonsReference;
    },
};
</script>

<style scoped>
.c-save-options {
    @apply
        flex
        items-center
        justify-end
        mt-4
    ;
}
</style>
