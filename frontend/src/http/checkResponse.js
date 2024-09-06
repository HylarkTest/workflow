import {
    curryRight, has, get, isUndefined, overSome, property, unary,
} from 'lodash';
import { ApolloError } from '@apollo/client';

/*
 * I'll be honest this file is really just me playing around with functional
 * programming using Lodash to see what it's like and if it's useful. I will
 * explain what is going on at each stage.
 * If this becomes a hassle to maintain then just change it to regular functions
 * there's no need to keep it like this, but the idea is that this way it is
 * more modular and easier to maintain. We'll see.
 */

/*
 * This is a group of functions that fetch a particular property from an object.
 * In this case fetching specific properties from an error, like status code.
 * These are equivalent to:
 *
 * export function getMessage(error) {
 *     return _.get(error, 'gqlError.message');
 * }
 */
export const getApiStatus = property('response.status');
export const getApolloStatus = property('networkError.response.status');
export const getMessage = property('gqlError.message');
export const getCategory = property('gqlError.extensions.category');

/*
 * Nothing fancy here just checking what type of error it is and using the
 * appropriate function to fetch the status.
 */
export const isApolloError = (error) => error instanceof ApolloError || has(error, 'graphQLErrors');
export const getStatus = (error) => (isApolloError(error) ? getApolloStatus(error) : getApiStatus(error));

/*
 * Again this is just a normal function that checks if the response is an error.
 */
export function responseHasErrorStatus(response, status) {
    const responseStatus = getStatus(response);
    if (!isUndefined(status)) {
        return responseStatus === status;
    }
    return responseStatus >= 300 || responseStatus <= 599 || responseStatus === 0;
}

/*
 * Same again with this one, checking if there is an error message in the
 * response.
 */
export function responseHasErrorMessage(response, message) {
    let messages = [];
    if (has(response, 'graphQLErrors')) {
        messages = get(response, 'graphQLErrors', []).map(property('message'));
    }
    if (has(response, 'networkError')) {
        messages.push(get(response, 'networkError.message'));
    }
    if (has(response, 'message')) {
        messages.push(response.message);
    }
    if (message) {
        return messages.includes(message);
    }
    return !!messages.length;
}

/*
 * Once again checking if a specific category exists in a response or if it has
 * any categories at all.
 */
export function responseHasCategory(response, category) {
    const categories = get(response, 'graphQLErrors', []).map(property('extensions.category'));
    if (categories) {
        return categories.includes(category);
    }
    return !!categories.length;
}

/*
 * Now we get into the interesting stuff.
 * Currying a function involves splitting it up into several functions for each
 * argument so you can then create a bunch of functions with one of the
 * arguments pre-populated. I'll explain the example here.
 * `curryRight` works the same way as `curry` except it reverses the order of
 * the arguments.
 * So in this case `curriedStatusCheck` is a function that accepts one argument
 * which will be the last argument passed to the `responseHasErrorStatus` (it
 * would be the first argument if we were using `curry`) so we can create a
 * bunch of functions that pre-populate the `status` argument.
 * An equivalent method would look like this:
 *
 * export function curriedStatusCheck(status) {
 *     return (response) => {
 *         return responseHasErrorStatus(response, status);
 *     }
 * }
 *
 * And that means `responseHasValidationErrorStatus` looks like this:
 *
 * export function responseHasValidationErrorStatus(response) {
 *     return responseHasErrorStatus(response, 422);
 * }
 *
 * And so on.
 */
const curriedStatusCheck = curryRight(responseHasErrorStatus);
const responseHasValidationErrorStatus = curriedStatusCheck(422);
const responseHasAuthorizationErrorStatus = curriedStatusCheck(403);
const responseHasMissingErrorStatus = curriedStatusCheck(404);
const responseHasAuthenticationErrorStatus = overSome(curriedStatusCheck(419), curriedStatusCheck(401));
function responseHasServerErrorStatus(response) {
    const status = getStatus(response);
    return status >= 500 || status < 600;
}

/*
 * These work in the same way as the previous ones except the `category`
 * argument from `responseHasCategory` is being pre-populated.
 */
const curriedCategoryCheck = curryRight(responseHasCategory);
const responseHasValidationCategory = curriedCategoryCheck('validation');
const responseHasRedirectCategory = curriedCategoryCheck('redirect');
const responseHasMissingCategory = curriedCategoryCheck('missing');
const responseHasAuthorizationCategory = curriedCategoryCheck('authorization');
const responseHasAuthenticationCategory = curriedCategoryCheck('authentication');
const responseHasServerCategory = overSome(
    curriedCategoryCheck('generic'),
    curriedCategoryCheck('internal'),
    curriedCategoryCheck('graphql')
);

/*
 * Nothing special about these functions aside from they use the generated
 * functions from before.
 */
export function isValidationError(error) {
    return responseHasValidationErrorStatus(error)
        || (isApolloError(error) && responseHasValidationCategory(error));
}

export function isRedirectError(error) {
    return (isApolloError(error) && responseHasRedirectCategory(error));
}

export function isAuthenticationError(error) {
    return responseHasAuthenticationErrorStatus(error)
        || (isApolloError(error) && responseHasAuthenticationCategory(error));
}

export function isAuthorizationError(error) {
    return responseHasAuthorizationErrorStatus(error)
        || (isApolloError(error) && responseHasAuthorizationCategory(error));
}

export function isServerError(error) {
    return responseHasServerErrorStatus(error)
        || (isApolloError(error) && responseHasServerCategory(error));
}

export function isMissingError(error) {
    return responseHasMissingErrorStatus(error)
        || (isApolloError(error) && responseHasMissingCategory(error));
}

export const isTooManyRequestsError = curriedStatusCheck(429);

/*
 * Same as the previous functions, just checking if the status is 0
 */
export const isConnectionError = overSome([
    curriedStatusCheck(0),
    (error) => responseHasErrorMessage(error, 'Network Error'),
]);

/*
 * `overSome` calls the provided functions in order and returns true if any of
 * them return true (exiting early) or false if none of them does.
 * `unary` takes the function passed to it and ensures it is only called with
 * one argument.
 * So this function is equivalent to:
 *
 * export function isHttpError(response) {
 *     return isApolloError(response) || responseHasErrorStatus(response);
 * }
 */
export const isHttpError = overSome(
    isApolloError,
    unary(responseHasErrorStatus)
);

/*
 * Same as the first group of functions that extract a property of the object
 * passed to the function.
 */
export const getRedirect = property('response.data.redirect');

/*
 * Nothing special here.
 */
export function getValidationMessages(error) {
    if (isApolloError(error)) {
        return get(error, 'graphQLErrors.0.extensions.validation');
    }
    return get(error, 'response.data.errors');
}

export function isLimitError(error) {
    return isValidationError(error)
        && _.has(getValidationMessages(error), 'limit');
}
