<template>
    <div
        class="o-access-form-base shadow-primary-600/20"
        :class="widthClass"
    >
        <slot name="top">
        </slot>

        <div class="mb-6">
            <h2
                v-t="title"
                class="o-access-form-base__title"
                :class="headerSizeClass || 'text-3xl'"
            >
            </h2>
            <p
                v-if="subtitle"
                v-t="subtitle"
                class="o-access-form-base__sub"
            >
            </p>
        </div>
        <FormWrapper
            :form="form"
            dontScroll
            @submit="goNext"
        >
            <slot>
            </slot>

            <div class="flex flex-col items-center mt-6">
                <button
                    v-t="buttonText"
                    class="o-access-form-base__button bg-azure-600 hover:bg-azure-500"
                    :class="{ 'opacity-25 pointer-events-none': buttonDisabled }"
                    :title="buttonTooltip"
                    :disabled="buttonDisabled"
                    type="submit"
                >
                </button>
                <div
                    v-if="footerLink"
                    class="flex mt-4 text-xs"
                >
                    <p
                        v-t="footerLink.text"
                        class="text-gray-400 font-semibold mr-1"
                    >
                    </p>
                    <router-link
                        v-t="footerLink.clickable"
                        :to="{ name: footerLink.link, query: footerLink.query || {} }"
                        class="font-bold text-azure-800 hover:underline"
                    >

                    </router-link>
                </div>

                <slot name="lowerSpace">
                </slot>
            </div>
        </FormWrapper>
    </div>
</template>

<script>

export default {
    name: 'AccessFormBase',
    components: {

    },
    mixins: [
    ],
    props: {
        form: {
            type: Object,
            required: true,
        },
        title: {
            type: String,
            required: true,
        },
        subtitle: {
            type: String,
            default: '',
        },
        buttonText: {
            type: String,
            required: true,
        },
        buttonTooltip: {
            type: String,
            default: '',
        },
        buttonDisabled: Boolean,
        footerLink: {
            type: [Object, null],
            default: null,
        },
        headerSizeClass: {
            type: String,
            default: '',
        },
        widthClass: {
            type: String,
            default: 'w-4/5',
        },
    },
    emits: [
        'goNext',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        goNext() {
            this.$emit('goNext');
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-access-form-base {
    max-width: 360px;

    @apply
        bg-cm-00
        px-10
        py-8
        rounded-xl
        shadow-xl
    ;

    &__title {
        @apply
            font-bold
            mb-2
            text-azure-950
        ;
    }

    &__sub {
        @apply
            leading-snug
            text-gray-600
            text-smbase
        ;
    }

    &__button {
        @apply
            font-semibold
            py-2
            rounded-xl
            text-center
            text-cm-00
            w-full
        ;
    }
}

@media (min-width: 768px) {
    .o-access-form-base {
        @apply
            bg-inherit
            shadow-none
        ;
    }

}

</style>
