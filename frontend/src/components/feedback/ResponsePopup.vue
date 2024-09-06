<template>
    <FeedbackPopup
        class="c-response-popup"
        :bgHoverColor="mainBgColor"
        :feedbackId="feedbackId"
    >
        <div class="flex flex-col items-center">
            <div
                class="c-response-popup__circle circle-center"
                :class="lightBgColor"
            >
                <i
                    class="far"
                    :class="[icon, mainTextColor]"
                >
                </i>
            </div>

            <p
                v-if="header && !isHtml"
                class="font-semibold mb-2 text-center"
            >
                {{ header }}
            </p>

            <p
                v-if="header && isHtml"
                v-dompurify-html="header"
                class="font-semibold mb-2 text-center"
            >
            </p>

            <p
                v-if="!hideMessage && message && !isHtml"
                class="text-center leading-snug text-cm-600 text-xssm"
            >
                {{ displayedCustomMessage }}

            </p>
            <p
                v-else-if="!hideMessage && message && isHtml"
                v-dompurify-html="displayedCustomMessage"
                class="text-center leading-snug text-cm-600 text-sm"
            >
            </p>
        </div>

        <component
            v-if="customComponent"
            :is="customComponent"
            v-bind="$attrs"
        >
        </component>

    </FeedbackPopup>
</template>

<script>

import FeedbackPopup from './FeedbackPopup.vue';

import providesColors from '@/vue-mixins/style/providesColors.js';

const icons = {
    ERROR: {
        icon: 'fa-exclamation-triangle',
        color: 'peach',
    },
    SAVED: {
        icon: 'fa-circle-check',
        color: 'emerald',
    },
    SUCCESS: {
        icon: 'fa-thumbs-up',
        color: 'emerald',
    },
    INFO: {
        icon: 'fa-square-info',
        color: 'sky',
        noMessage: true,
    },
    LIMIT: {
        icon: 'fa-star-exclamation',
        color: 'gold',
    },
    WARNING: {
        icon: 'fa-diamond-exclamation',
        color: 'gold',
    },
    VALIDATION: {
        icon: 'fa-octagon-exclamation',
        color: 'violet',
        noMessage: true,
    },
};

export default {
    name: 'ResponsePopup',
    components: {
        FeedbackPopup,
    },
    mixins: [
        providesColors,
    ],
    props: {
        responseType: {
            type: String,
            default: 'ERROR',
            validator(val) {
                return [
                    'ERROR',
                    'SUCCESS',
                    'INFO',
                    'SAVED',
                    'LIMIT',
                    'WARNING',
                    'VALIDATION',
                ].includes(val);
            },
        },
        customHeaderPath: {
            type: [String, Object],
            default: '',
        },
        customMessagePath: {
            type: [String, Object],
            default: '',
        },
        customMessageString: {
            type: String,
            default: '',
        },
        customIcon: {
            type: String,
            default: '',
        },
        feedbackId: {
            type: String,
            required: true,
        },
        customComponent: {
            type: [String, Object],
            default: '',
        },
        isHtml: Boolean,
        hideMessage: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        header() {
            if (this.customHeaderPath) {
                if (_.isObject(this.customHeaderPath)) {
                    return this.formatObjectLanguage(this.customHeaderPath);
                }
                return this.$t(this.customHeaderPath);
            }
            return this.$t(this.responseHeaderPath);
        },
        responseHeaderPath() {
            return `feedback.responses.${_.camelCase(this.responseType)}.header`;
        },
        message() {
            return this.customMessageString || this.messagePath;
        },
        messagePath() {
            return this.customMessagePath || this.responseMessagePath;
        },
        noMessage() {
            return icons[this.responseType].noMessage;
        },
        responseMessagePath() {
            if (!this.noMessage) {
                return `feedback.responses.${_.camelCase(this.responseType)}.defaultMessage`;
            }
            return '';
        },
        icon() {
            return this.customIcon || icons[this.responseType].icon;
        },

        color() {
            return icons[this.responseType].color;
        },
        mainTextColor() {
            return this.getTextColor(this.color);
        },
        mainBgColor() {
            return this.getBgColor(this.color);
        },
        lightBgColor() {
            return this.getBgColor(this.color, '100');
        },
        displayedCustomMessage() {
            if (this.customMessageString) {
                return this.customMessageString;
            }
            if (_.isObject(this.messagePath)) {
                return this.formatObjectLanguage(this.messagePath);
            }
            return this.$t(this.messagePath);
        },
    },
    methods: {
        formatObjectLanguage(path) {
            return this.$t(path.path, path.args);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-response-popup {
    @apply
        p-4
    ;

    &__circle {
        height: 60px;
        width: 60px;

        @apply
            mb-2
            text-2xl
        ;
    }
}

</style>
