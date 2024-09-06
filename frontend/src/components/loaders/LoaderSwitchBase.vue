<template>
    <LoaderProcessingAccount>
        <div class="relative z-over">
            <div class="circle-center bg-secondary-100 h-48 w-48">
                <i
                    class="far fa-shuffle text-secondary-600 text-[100px]"
                >
                </i>
            </div>
            <p
                class="mt-8 text-center text-2xl font-semibold text-secondary-600 flex justify-center items-end"
            >
                {{ $t('feedback.switchingBases') }}
                <i class="fas fa-ellipsis fa-beat ml-2 -mb-0.5">
                </i>
            </p>
        </div>
    </LoaderProcessingAccount>
</template>

<script>
import LoaderProcessingAccount from '@/components/loaders/LoaderProcessingAccount.vue';

export default {
    name: 'LoaderSwitchBase',
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
/* .c-loader-signout {
} */
</style>
