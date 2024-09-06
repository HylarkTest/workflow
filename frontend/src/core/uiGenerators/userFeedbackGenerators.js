import { markRaw } from 'vue';
import { randomNumber } from '@/core/utils.js';
import FeedbackErrors from '@/components/feedback/FeedbackErrors.vue';
import { addFeedback, closeFeedback } from '@/core/feedback.js';
import { getValidationMessages, isValidationError } from '@/http/checkResponse.js';

function feedback(componentName, props, duration = 10000, isDebounced = null) {
    const id = randomNumber();
    // Trying to avoid that pesky dependency cycle
    addFeedback({
        componentName,
        props,
        id,
    }, isDebounced);

    if (duration) {
        window.setTimeout(() => {
            closeFeedback(id);
        }, duration);
        // Return id to give the caller the ability to close the feedback some
        // other way
    }
}

export function saveFeedback(props, duration = 4000, isDebounced = null) {
    feedback('ResponsePopup', { responseType: 'SAVED', ...props }, duration, isDebounced);
}

export function debouncedSaveFeedback(isDebounced = true, props = {}, duration = 4000) {
    saveFeedback(props, duration, isDebounced);
}

export function successFeedback(props, duration = 4000, isDebounced = null) {
    feedback('ResponsePopup', { responseType: 'SUCCESS', ...props }, duration, isDebounced);
}

export function errorFeedback(props, duration = 6000, isDebounced = null) {
    feedback('ResponsePopup', { responseType: 'ERROR', ...props }, duration, isDebounced);
}

export function limitFeedback(props, duration = 8000, isDebounced = null) {
    feedback('ResponsePopup', { responseType: 'LIMIT', ...props }, duration, isDebounced);
}

export function warningFeedback(props, duration = 6000, isDebounced = null) {
    feedback('ResponsePopup', { responseType: 'WARNING', ...props }, duration, isDebounced);
}

export function infoFeedback(props, duration = 6000, isDebounced = null) {
    feedback('ResponsePopup', { responseType: 'INFO', ...props }, duration, isDebounced);
}

export function validationFeedback(messages, props = {}, duration = 6000, isDebounced = null) {
    feedback('ResponsePopup', {
        responseType: 'VALIDATION',
        customComponent: markRaw(FeedbackErrors),
        errors: messages,
        ...props,
    }, duration, isDebounced);
}

export function reportValidationError(error, fields = [], rethrow = true) {
    if (isValidationError(error)) {
        const fieldsArray = _.isArray(fields) ? fields : [fields];
        const messages = getValidationMessages(error);
        _.forEach(messages, (message, key) => {
            if (fieldsArray.length && !fieldsArray.includes(key)) {
                return;
            }
            validationFeedback(messages[key]);
        });
    }
    if (rethrow) {
        throw error;
    }
}

export default function install(baseApp) {
    // eslint-disable-next-line no-param-reassign
    baseApp.config.globalProperties.$feedback = feedback;

    // eslint-disable-next-line no-param-reassign
    baseApp.config.globalProperties.$saveFeedback = saveFeedback;

    // eslint-disable-next-line no-param-reassign
    baseApp.config.globalProperties.$debouncedSaveFeedback = debouncedSaveFeedback;

    // eslint-disable-next-line no-param-reassign
    baseApp.config.globalProperties.$successFeedback = successFeedback;

    // eslint-disable-next-line no-param-reassign
    baseApp.config.globalProperties.$limitFeedback = limitFeedback;

    // eslint-disable-next-line no-param-reassign
    baseApp.config.globalProperties.$warningFeedback = warningFeedback;

    // eslint-disable-next-line no-param-reassign
    baseApp.config.globalProperties.$errorFeedback = errorFeedback;

    // eslint-disable-next-line no-param-reassign
    baseApp.config.globalProperties.$infoFeedback = infoFeedback;

    // eslint-disable-next-line no-param-reassign
    baseApp.config.globalProperties.$validationFeedback = validationFeedback;
}
