<template>
    <div class="c-error-template centered">
        <div
            v-if="image"
            class="mb-4"
        >
            <BirdImage
                class="h-40"
                :whichBird="image"
                :altProp="imageAlt"
            >
            </BirdImage>
        </div>
        <div
            v-else
            class="c-error-template__icon"
        >
            <i
                class="fal fa-user-robot-xmarks"
            >
            </i>
        </div>
        <div
            v-if="$slots.status"
            class="c-error-template__status"
        >
            <slot
                name="status"
            >
            </slot>
        </div>
        <div class="mb-6 text-center">
            <div
                v-if="$slots.message"
                class="c-error-template__message"
            >
                <slot
                    name="message"
                >
                </slot>
            </div>

            <div
                v-if="$slots.explanation"
                class="c-error-template__explanation"
            >
                <slot
                    name="explanation"
                >
                </slot>
            </div>
        </div>
        <template
            v-if="link"
        >
            <a
                v-if="typeof link === 'string' && link.startsWith('http')"
                :href="link"
                rel="noreferrer noopener"
                class="button bg-azure-600 hover:bg-azure-500 text-cm-00"
            >
                {{ $t(linkTextPath) }}
            </a>
            <router-link
                v-else
                v-t="linkTextPath"
                :to="link"
                class="button bg-azure-600 hover:bg-azure-500 text-cm-00"
            >
            </router-link>
        </template>
    </div>
</template>

<script>

export default {
    name: 'ErrorTemplate',
    components: {

    },
    mixins: [
    ],
    props: {
        customLink: {
            type: String,
            default: '',
        },
        customLinkTextPath: {
            type: String,
            default: '',
        },
        isAuthenticated: Boolean,
        image: {
            type: String,
            default: '',
        },
        imageAlt: {
            type: String,
            default: '',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        link() {
            if (this.customLink) {
                return this.customLink;
            }
            if (this.isAuthenticated) {
                return { name: 'home' };
            }
            return { name: 'home' };
        },
        linkTextPath() {
            if (this.customLinkTextPath) {
                return this.customLinkTextPath;
            }
            return 'errors.backHome';
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-error-template {
    max-width: 500px;

    @apply
        flex-col
    ;

    &__icon {
        font-size: 100px;

        @apply
            text-cm-300
        ;
    }

    &__status {
        font-size: 120px;

        @apply
            font-semibold
            leading-none
            mb-4
            text-azure-800
        ;
    }

    &__message {
        @apply
            font-semibold
            mb-1
            text-lg
        ;
    }

    &__explanation {
        @apply
            text-cm-600
            text-smbase
        ;
    }
}

</style>
