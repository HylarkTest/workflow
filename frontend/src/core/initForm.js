import axios from 'axios';
import Form from 'formla';
import { $t } from '@/i18n.js';
import { getValidationMessages, isValidationError } from '@/http/checkResponse.js';

function isFile(item) {
    return item instanceof File || item instanceof Blob;
}

export function isTooBig(val, maxSize) {
    return isFile(val) && val.size >= (maxSize * 1024 * 1024);
}

export function getFiles(obj, prefix = '') {
    let files = {};
    let entries = [];
    if (obj instanceof FormData) {
        entries = _.isFunction(obj.entries) ? obj.entries() : obj.entries;
    } else {
        entries = Object.entries(obj);
    }
    Array.from(entries).forEach(([key, value]) => {
        if (isFile(value)) {
            files[`${prefix}${key}`] = value;
        }
        if (_.isObject(value) || _.isArray(value)) {
            files = {
                ...files,
                ...getFiles(value, `${prefix}${key}.`),
            };
        }
    });

    return files;
}

export default function initForm() {
    Form.setOptions({
        clear: false,
        useJson: true,
        sendWith(method, url, data, options) {
            const files = getFiles(data);

            const sizeErrors = {};

            _.forEach(files, (file, path) => {
                const maxUpload = options.maxUpload || 2;
                if (isTooBig(file, maxUpload)) {
                    sizeErrors[path] = [$t('validation.fileSize', { max: maxUpload })];
                }
            });

            if (!_.isEmpty(sizeErrors)) {
                const error = new Error('Validation failed for image.');
                error.response = {
                    data: { errors: sizeErrors },
                    status: 422,
                };
                return Promise.reject(error);
            }
            return axios({
                method,
                url,
                data,
                ...options,
            });
        },
        isValidationError,
        formatErrorResponse: getValidationMessages,
    });
}
