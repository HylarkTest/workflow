<template>
    <div class="o-import-progress">
        <p class="text-center font-bold text-lg text-primary-600 mb-2">
            {{ $t('imports.headers.inProgress') }}
        </p>

        <div
            class="centered mb-4"
        >
            <p class="text-center max-w-[600px] text-smbase">
                {{ $t('imports.youMayLeave') }}
            </p>
        </div>

        <div class="centered mb-8">
            <div>
                <p>
                    <span
                        class="font-semibold"
                    >
                        {{ $t('imports.importName') }}:
                    </span>
                    {{ importName }}
                </p>
                <p>
                    <span
                        class="font-semibold"
                    >
                        {{ $t('labels.blueprint') }}:
                    </span>
                    {{ mappingName }}
                </p>
                <p>
                    <span
                        class="font-semibold"
                    >
                        {{ $t('imports.headers.fileName') }}:
                    </span>
                    {{ fileName }}
                </p>
                <p>
                    <span
                        class="font-semibold"
                    >
                        {{ $t('imports.headers.progressTime') }}:
                    </span>
                    {{ progressTime }}
                </p>
            </div>
        </div>
        <div
            class="centered"
        >
            <LoadingBar
                :percentage="progressPercentage"
                barSizeClasses="h-8 w-full sm:w-1/2"
            >
            </LoadingBar>
        </div>
    </div>
</template>

<script>

import LoadingBar from '@/components/loaders/LoadingBar.vue';

export default {
    name: 'ImportProgress',
    components: {
        LoadingBar,
    },
    mixins: [
    ],
    props: {
        importProgress: {
            type: Object,
            required: true,
        },
        importName: {
            type: String,
            default: '',
        },
        mappingName: {
            type: String,
            default: '',
        },
        fileName: {
            type: String,
            default: '',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        progressPercentage() {
            const progress = this.importProgress?.progress;
            const percentage = progress ? Math.floor(progress * 100) : 0;
            return percentage < 10 ? 10 : percentage;
        },
        progressTime() {
            const estimatedTimeInSeconds = this.importProgress?.estimatedTimeRemaining;
            if (!estimatedTimeInSeconds) {
                return 'Calculating...';
            }
            const minutes = Math.round(estimatedTimeInSeconds / 60);
            const hours = Math.round(minutes / 60);
            if (hours) {
                return `~${hours} hours`;
            }
            if (minutes) {
                return `~${minutes} minutes`;
            }
            const seconds = estimatedTimeInSeconds % 60;
            return `~${seconds} seconds`;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.o-import-progress {

} */

</style>
