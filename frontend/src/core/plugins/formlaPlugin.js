import Form from 'formla';
import { getCachedOperationNames, removeTypename } from '@/core/helpers/apolloHelpers.js';
import { $t } from '@/i18n.js';
import { isValidationError } from '@/http/checkResponse.js';
import { limitFeedback } from '@/core/uiGenerators/userFeedbackGenerators.js';
import { reportUnhandledValidationError } from '@/http/exceptionHandler.js';
import { getFiles, isTooBig } from '@/core/initForm.js';
import { activeBase, baseApolloClient } from '@/core/repositories/baseRepository.js';

export function createApolloForm(apolloClient, formData, formOptions) {
    const form = new Form(formData, {
        sendWith(method, url, data, options) {
            let variables = _.has(data, 'variables') ? data.variables : data;

            const files = getFiles(variables);

            const sizeErrors = {};

            _.forEach(files, (file, path) => {
                const maxUpload = options.maxUpload || 2;
                if (isTooBig(file, maxUpload)) {
                    sizeErrors[path] = [$t('validation.fileSize', { max: maxUpload })];
                }
            });

            if (!_.isEmpty(sizeErrors)) {
                const error = new Error('Validation failed for file.');
                error.graphQLErrors = [{
                    extensions: {
                        category: 'validation',
                        validation: sizeErrors,
                    },
                }];
                error.response = { status: 422 };
                return Promise.reject(error);
            }

            if (_.get(options, 'wrapInput', true)) {
                variables = { input: variables };
            }

            return apolloClient.mutate({
                mutation: options.query || data.query,
                variables,
                ...options,
                ...(options.refetchQueries ? {
                    refetchQueries: getCachedOperationNames(options.refetchQueries, apolloClient),
                } : {}),
            }).catch((error) => {
                if (isValidationError(error)) {
                    const messages = error.graphQLErrors[0].extensions.validation;
                    if (_.has(messages, 'limit')) {
                        limitFeedback();
                    }
                }
                if (options.reportValidation) {
                    reportUnhandledValidationError(error);
                }
                throw error;
            });
        },
        formatData(data) {
            return removeTypename(data);
        },
        formatErrorResponse({ graphQLErrors: errors }) {
            const messages = errors[0].extensions.validation;
            return _.mapKeys(messages, (_, key) => key.replace('input.', ''));
        },
        isValidationError({ graphQLErrors: errors }) {
            return errors?.length && errors[0].extensions?.category === 'validation';
        },
        useJson: true,
        strictMode: true,
        ...formOptions,
    });

    form._apolloClient = apolloClient;

    return form;
}

export default function install(app) {
    // eslint-disable-next-line no-param-reassign
    app.config.globalProperties.$apolloForm = function localCreateApolloForm(data, options) {
        let client;
        if (options?.client) {
            client = _.isObject(options.client)
                ? options.client
                : app.config.globalProperties.$apolloProvider.clients[options.client];
        } else {
            client = baseApolloClient();
        }
        return createApolloForm(client, data, options);
    };

    // eslint-disable-next-line no-param-reassign
    app.config.globalProperties.$form = function createForm(formData, formOptions) {
        const activeBaseId = activeBase()?.id;
        return new Form(formData, {
            ...formOptions,
            headers: {
                ...formOptions?.headers,
                'X-Base-Id': activeBaseId,
            },
        });
    };
}
