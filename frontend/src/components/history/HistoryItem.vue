<template>
    <div class="o-history-item flex">

        <p
            class="o-history-item__time"
        >
            {{ $dayjs(action.createdAt).format('LT') }}
        </p>
        <div
            class="relative shrink-0"
            :class="isSingleItemHistory ? 'pr-1' : 'pr-5'"
        >
            <div
                v-if="!isLast"
                class="o-history-item__bar"
                :class="bgHistoryColor"
            >
            </div>
            <div
                class="o-history-item__circle"
                :class="borderHistoryColor"
            >
            </div>
        </div>
        <div
            class="flex-1"
            :class="{ 'mb-5': !isLast }"
        >
            <div
                class="flex items-center mb-2"
            >
                <div
                    v-if="performer"
                    class="mr-1 bg-cm-100 pr-2 rounded-lg"
                >
                    <ProfileNameImage
                        :profile="performer"
                        :hideFullName="false"
                        size="sm"
                        colorName="turquoise"
                    >
                    </ProfileNameImage>
                </div>
                <p
                    v-if="!isSingleItemHistory"
                    class="o-history-item__description text-cm-700 font-semibold"
                >
                    - {{ action.description }}
                </p>
            </div>
            <ol
                v-if="changes"
                class="ml-2"
            >
                <li
                    v-for="(change, index) in changes"
                    :key="index"
                    class="mb-1"
                >
                    <HistoryDetails
                        :change="change"
                    >
                    </HistoryDetails>
                </li>
            </ol>
        </div>
    </div>
</template>

<script>

import HistoryDetails from '@/components/history/HistoryDetails.vue';

import providesHistoryColors from '@/vue-mixins/providesHistoryColors.js';
import ProfileNameImage from '@/components/profile/ProfileNameImage.vue';

export default {

    name: 'HistoryItem',
    components: {
        ProfileNameImage,
        HistoryDetails,
    },
    mixins: [
        providesHistoryColors,
    ],
    props: {
        action: {
            type: Object,
            required: true,
        },
        isLast: Boolean,
        isSingleItemHistory: Boolean,
    },
    data() {
        return {
        };
    },
    computed: {
        bgHistoryColor() {
            return this.historyBgColor(this.action.type);
        },
        borderHistoryColor() {
            return this.historyBorderColor(this.action.type);
        },
        name() {
            return this.performer.name;
        },
        performer() {
            return this.action.performer;
        },
        changes() {
            if (this.isSingleItemHistory && !this.action.changes) {
                return [{ description: this.action.description }];
            }
            return this.action.changes;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>
.o-history-item {
    @apply
        leading-snug
    ;

    &__time {
        width: 70px;

        @apply
            font-medium
            shrink-0
            text-cm-400
        ;
    }

    &__bar {
        left: 7px;
        width: 2px;

        @apply
            absolute
            h-full
            rounded-full
            top-0
        ;
    }

    &__circle {
        @apply
            bg-cm-00
            border-2
            border-solid
            h-4
            relative
            rounded-full
            w-4
        ;
    }

    &__description {
        word-break: break-word;
    }

    /*
    &__more {

    }
     */
}
</style>
