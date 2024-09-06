<template>
    <LoaderProcessingAccount>
        <div class="relative z-over">
            <div class="circle-center bg-primary-100 h-48 w-48">
                <i
                    class="far fa-arrow-right-from-bracket text-primary-600 text-[100px]"
                >
                </i>
            </div>
            <p
                class="mt-8 text-center text-2xl font-semibold text-primary-600 flex justify-center items-end"
            >
                {{ $t('feedback.signingOut') }}
                <i class="fas fa-ellipsis fa-beat ml-2 -mb-0.5">
                </i>
            </p>
        </div>
    </LoaderProcessingAccount>
</template>

<script>
import LoaderProcessingAccount from '@/components/loaders/LoaderProcessingAccount.vue';

export default {
    name: 'LoaderSignout',
    components: {
        LoaderProcessingAccount,
    },
    mixins: [
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

.c-loader-signout {
    @apply
        bottom-0
        fixed
        h-full
        left-0
        pointer-events-none
        w-full
        z-mega
    ;
}

</style>
