<template>
    <div class="o-previous-import flex-col md:flex-row flex items-baseline gap-2 text-sm">
        <div class="w-40">
            <div
                class="mr-6 font-semibold py-2 px-4 rounded inline"
                :class="statusColorClasses"
            >
                <i
                    class="fa-regular mr-1"
                    :class="statusIcon"
                >
                </i>
                {{ statusText }}
            </div>
        </div>
        <div class="flex-1">
            <div class="o-previous-import__name font-semibold text-lg mb-1">
                {{ dataImport.name }}
            </div>
            <div class="o-previous-import__file">
                {{ dataImport.filename }}
            </div>
            <div class="o-previous-import__date">
                <span class="font-semibold text-sm text-primary-500">
                    {{ _t('imports.headers.importStartedAt') }}:
                </span>
                {{ formattedStart }}
            </div>
            <div
                v-if="finishedAt"
                class="o-previous-import__date"
            >
                <span class="font-semibold text-sm text-primary-500">
                    {{ _t('imports.headers.importCompletedAt') }}:
                </span>
                {{ formattedFinish }}
            </div>

            <div
                v-if="revertStartedAt"
                class="border-t border-solid border-primary-300 mt-2 pt-2"
            >
                <div
                    class="o-previous-import__date"
                >
                    <span class="font-semibold text-sm text-primary-500">
                        {{ _t('imports.headers.revertStartedAt') }}:
                    </span>
                    {{ formattedRevertStart }}
                </div>
                <div
                    v-if="revertFinishedAt"
                    class="o-previous-import__date"
                >
                    <span class="font-semibold text-sm text-primary-500">
                        {{ _t('imports.headers.revertCompletedAt') }}:
                    </span>
                    {{ formattedRevertFinish }}
                </div>
            </div>

            <div
                v-if="showProgress"
            >
                <div
                    class="o-previous-import__date"
                >
                    <span class="font-semibold text-sm text-primary-500">
                        {{ _t('labels.progress') }}:
                    </span>
                    {{ progressPercentage }}%
                </div>

                <LoadingBar
                    class="mt-1"
                    :percentage="progressPercentage"
                    barSizeClasses="h-4 w-full sm:w-1/2"
                >
                </LoadingBar>
            </div>

            <ProfileNameImage
                class="mt-3"
                :profile="member"
                colorName="turquoise"
            >
            </ProfileNameImage>
        </div>
        <div
            class="flex"
        >
            <button
                v-if="showCancel"
                class="button button-peach mr-3 last:mr-0"
                type="button"
                @click="openCancelModal"
            >
                <i
                    class="fa-regular mr-1"
                    :class="cancelledIcon"
                >
                </i>

                {{ _t('common.cancel') }}
            </button>

            <button
                v-if="showRevert"
                class="button bg-violet-600 text-cm-00 hover:bg-violet-500"
                type="button"
                @click="openModal"
            >
                <i
                    class="fa-regular mr-1"
                    :class="revertingIcon"
                >
                </i>

                {{ _t('common.revert') }}
            </button>
        </div>

        <ConfirmModal
            v-if="isModalOpen"
            :icon="revertConfirmIcon"
            :processing="processingRevert"
            @closeModal="closeModal"
            @cancelAction="closeModal"
            @proceedWithAction="revert"
        >
            <p class="mb-3">
                {{ _t('imports.revert.1') }}
            </p>

            <p class="mb-3">
                {{ _t('imports.revert.2') }}
            </p>

            <p class="mb-3">
                {{ _t('common.wishToContinue') }}
            </p>
        </ConfirmModal>

        <ConfirmModal
            v-if="isCancelModalOpen"
            :icon="cancelConfirmIcon"
            :processing="processingCancel"
            @closeModal="closeCancelModal"
            @cancelAction="closeCancelModal"
            @proceedWithAction="cancel"
        >
            <p class="mb-3">
                {{ _t('imports.cancel.1') }}
            </p>

            <p class="mb-3">
                {{ _t('imports.cancel.2') }}
            </p>

            <p class="mb-3">
                {{ _t('common.wishToContinue') }}
            </p>
        </ConfirmModal>
    </div>
</template>

<script setup>

import {
    computed,
    ref,
} from 'vue';

import { useSubscription } from '@vue/apollo-composable';
import ProfileNameImage from '@/components/profile/ProfileNameImage.vue';
import LoadingBar from '@/components/loaders/LoadingBar.vue';
import ConfirmModal from '@/components/assets/ConfirmModal.vue';

import {
    convertDateFromUtcToTimezone,
} from '@/core/dateTimeHelpers.js';

import {
    timezone,
} from '@/core/repositories/preferencesRepository.js';

import PROGRESS_TRACKER_UPDATED from '@/graphql/progress-tracker/ProgressTrackerUpdated.gql';

import {
    cancelImport,
    revertImport,
} from '@/core/repositories/importsRepository.js';

import { _t } from '@/i18n.js';

// Apollo

const statusMap = {
    COMPLETED: {
        color: 'emerald',
        icon: 'fa-circle-check',
    },
    STARTED: {
        color: 'sky',
        icon: 'fa-circle-play',
    },
    CANCELLED: {
        color: 'peach',
        icon: 'fa-ban',
    },
    CANCELLING: {
        color: 'peach',
        icon: 'fa-ban',
    },
    REVERTED: {
        color: 'gold',
        icon: 'fa-arrow-rotate-left',
    },
    REVERTING: {
        color: 'violet',
        icon: 'fa-arrows-rotate-reverse',
    },
    FAILED: {
        color: 'rose',
        icon: 'fa-file-xmark',
    },
};

const props = defineProps({
    dataImport: {
        type: Object,
        required: true,
    },
});

const progressObj = computed(() => {
    return props.dataImport.progress;
});

const status = computed(() => {
    return progressObj.value.status;
});

const statusObj = computed(() => {
    return statusMap[status.value];
});

const isStarted = computed(() => {
    return progressObj.value.status === 'STARTED';
});

const isFinished = computed(() => {
    return progressObj.value.status === 'COMPLETED';
});

const showCancel = computed(() => {
    return isStarted.value;
});

const showProgress = computed(() => {
    return ['STARTED', 'CANCELLING', 'REVERTING'].includes(status.value);
});

const statusText = computed(() => {
    const camelStatus = _.camelCase(status.value);
    return _t(`labels.${camelStatus}`);
});

const statusColor = computed(() => {
    return statusObj.value.color;
});

const statusIcon = computed(() => {
    return statusObj.value.icon;
});

const cancelledIcon = computed(() => {
    return statusMap.CANCELLED.icon;
});

const cancelConfirmIcon = computed(() => {
    return `fa-regular ${cancelledIcon.value}`;
});

const statusColorClasses = computed(() => {
    return `bg-${statusColor.value}-100 text-${statusColor.value}-600`;
});

const member = computed(() => {
    return props.dataImport.member;
});

const startedAt = computed(() => {
    return progressObj.value.startedAt;
});

const finishedAt = computed(() => {
    return progressObj.value.finishedAt;
});

// const cancelledAt = computed(() => {
//     return progressObj.value.cancelledAt;
// });

const timeFormat = computed(() => {
    return utils.timeDayjsFormat();
});

const dateTimeFormat = computed(() => {
    return `LL ${timeFormat.value}`;
});

const formattedStart = computed(() => {
    return convertDateFromUtcToTimezone(startedAt.value, timezone.value, dateTimeFormat.value);
});

const formattedFinish = computed(() => {
    return convertDateFromUtcToTimezone(finishedAt.value, timezone.value, dateTimeFormat.value);
});

const revertStartedAt = computed(() => {
    return progressObj.value.revertedAt;
});

const revertFinishedAt = computed(() => {
    return progressObj.value.revertFinishedAt;
});

const formattedRevertStart = computed(() => {
    return convertDateFromUtcToTimezone(revertStartedAt.value, timezone.value, dateTimeFormat.value);
});

const formattedRevertFinish = computed(() => {
    return convertDateFromUtcToTimezone(revertFinishedAt.value, timezone.value, dateTimeFormat.value);
});

const progressPercentage = computed(() => {
    const progress = progressObj.value.progress;
    const percentage = progress ? Math.floor(progress * 100) : 0;
    return percentage < 10 ? 10 : percentage;
});

// Revert

const revertingIcon = computed(() => {
    return statusMap.REVERTING.icon;
});

const revertConfirmIcon = computed(() => {
    return `fa-regular ${revertingIcon.value}`;
});

const hasRevertStatus = computed(() => {
    return status.value === 'REVERTED' || status.value === 'REVERTING';
});

const showRevert = computed(() => {
    return isFinished.value && !hasRevertStatus.value;
});

const processingRevert = ref(false);

const isModalOpen = ref(false);

function openModal() {
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
}

async function revert() {
    processingRevert.value = true;
    try {
        await revertImport(props.dataImport.id);
        closeModal();
    } finally {
        processingRevert.value = false;
    }
}

// Cancel

const processingCancel = ref(false);

const isCancelModalOpen = ref(false);

function openCancelModal() {
    isCancelModalOpen.value = true;
}

function closeCancelModal() {
    isCancelModalOpen.value = false;
}

async function cancel() {
    processingCancel.value = true;
    try {
        await cancelImport(props.dataImport.id);
        closeCancelModal();
    } finally {
        processingCancel.value = false;
    }
}

useSubscription(PROGRESS_TRACKER_UPDATED, () => ({
    variables: {
        taskId: props.dataImport.id,
    },
    skip: !showProgress.value,
}));

</script>

<style scoped>

/*.o-previous-import {

} */

</style>
