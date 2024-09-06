<template>
    <div class="o-creation-wizard flex flex-col h-full">
        <div class="flex justify-between px-8 items-center py-1">
            <button
                class="button--lg button-primary flex items-center"
                :class="seeBack ? 'opacity-100' : 'opacity-0 pointer-events-none'"
                type="button"
                @click="$emit('goBack')"
            >
                <i class="far fa-angles-left mr-2">
                </i>

                {{ $t('common.back') }}
            </button>

            <slot
                name="middle"
            >
            </slot>

            <button
                class="button--lg button-primary flex items-center"
                :class="seeNext ? 'opacity-100' : 'opacity-0 pointer-events-none'"
                type="button"
                :disabled="processing || !seeNext"
                @click="$emit('goNext')"
            >
                {{ $t(nextTextPath) }}
                <i class="far fa-angles-right ml-2">
                </i>
            </button>
        </div>

        <div class="overflow-y-auto flex-1 px-8 h-full w-screen overflow-x-hidden">
            <slot>
            </slot>
        </div>

        <FullLoaderProcessing
            v-if="processing"
        >
            <div class="font-bold text-primary-600 z-over text-2xl mb-12">
                <slot
                    name="processingText"
                >
                </slot>
            </div>
        </FullLoaderProcessing>
    </div>
</template>

<script>

import FullLoaderProcessing from '@/components/loaders/FullLoaderProcessing.vue';

export default {
    name: 'CreationWizard',
    components: {
        FullLoaderProcessing,
    },
    mixins: [
    ],
    props: {
        seeBack: Boolean,
        seeNext: Boolean,
        nextTextPath: {
            type: String,
            default: 'common.next',
        },
        processing: Boolean,
    },
    emits: [
        'goBack',
        'goNext',
    ],
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

<style>

.o-creation-wizard {
    &__header {
        font-size: 50px;

        @apply
            font-bold
            my-10
            text-center
            text-primary-800
        ;

        &--sm {
            font-size: 40px;

            @apply
                font-bold
                my-10
                text-center
                text-primary-800
            ;
        }
    }

    &__prompt {
        @apply
            font-semibold
            mb-6
            text-2xl
            text-center
            text-cm-500
        ;
    }

    &__description {
        @apply
            block
            font-medium
            text-cm-500
        ;
    }
}

</style>
