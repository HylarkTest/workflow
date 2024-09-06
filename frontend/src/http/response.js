/* eslint-disable no-alert */
import {
    startsWith,
} from 'lodash';
import {
    getRedirect,
    isAuthenticationError,
    isConnectionError,
    isValidationError,
    isRedirectError,
} from '@/http/checkResponse.js';
import { errorFeedback } from '@/core/uiGenerators/userFeedbackGenerators.js';
import router from '@/router.js';

export function handleMissingError(error, redirect = true) {
    if (redirect) {
        router.replace('/404');
    } else {
        errorFeedback({
            customHeaderPath: 'feedback.responses.missing.header',
        });
    }
}

// eslint-disable-next-line import/prefer-default-export
export function handleHttpError(error, bubble = true) {
    const redirect = getRedirect(error);
    if (redirect && (startsWith(redirect, window.location.origin) || startsWith(redirect, '/'))) {
        window.location.href = redirect;
    }
    if (isAuthenticationError(error)) {
        window.redirectGuest();
        return true;
    }
    if (isValidationError(error)) {
        return true;
    }
    if (isRedirectError(error)) {
        return true;
    }
    if (isConnectionError(error)) {
        errorFeedback({
            isHtml: true,
            customMessagePath: 'feedback.responses.network.explanation',
            customHeaderPath: 'feedback.responses.network.message',
        }, 6000, true);
        return true;
    }

    errorFeedback({}, 6000, true);

    if (bubble) {
        if (error instanceof Error) {
            throw error;
        }
        return Promise.reject(error);
    }
    return false;
}

export function handleValidationError(error) {
    if (!isValidationError(error)) {
        throw error;
    }
}
