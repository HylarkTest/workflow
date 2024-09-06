// import { memoize } from 'lodash';
import { handleHttpError, handleMissingError } from '@/http/response.js';
import { isHttpError, isMissingError, isValidationError } from '@/http/checkResponse.js';
import { errorFeedback } from '@/core/uiGenerators/userFeedbackGenerators.js';
import config from '@/core/config.js';

const exceptionHandler = ((error, bubble = false) => {
    let httpError = error;
    if (error instanceof ErrorEvent) {
        httpError = error.error;
    } else if (error instanceof PromiseRejectionEvent) {
        httpError = error.reason;
    }
    if (isHttpError(httpError)) {
        return handleHttpError(httpError, bubble);
    }
    if (bubble) {
        if (error instanceof Error) {
            throw error;
        }
        return Promise.reject(error);
    }
    return false;
});

export default exceptionHandler;

export function handleVueError(error/* , vm, text */) {
    const handled = exceptionHandler(error);
    // Sentry will log the error, so to avoid two messages, we check if sentry
    // is enabled here.
    if (!handled && !config('sentry.report')) {
        // eslint-disable-next-line no-console
        console.error(error);
    }
}

export function checkAndHandleMissingError(error, redirect = true) {
    if (isMissingError(error)) {
        handleMissingError(error, redirect);
        return true;
    }
    return false;
}

export function pageQueryHandler(error) {
    checkAndHandleMissingError(error);
    return false;
}

export function reportUnhandledValidationError(error) {
    if (isValidationError(error)) {
        errorFeedback();
        // eslint-disable-next-line no-param-reassign
        error.force = true;
    }
}
