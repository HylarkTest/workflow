import { some } from 'lodash';
import config from '@/core/config.js';

// These are urls that are defined on the server and should not be hit
// by the dev build when working on local
const DEV_PROXY = [
    // eslint-disable-next-line prefer-regex-literals
    new RegExp('/email/verify/\\d+/[\\w\\d]+'),
    // eslint-disable-next-line prefer-regex-literals
    new RegExp('/email/verification-notification'),
];

export default function redirectToProxyMiddleware(to) {
    if (some(DEV_PROXY, (rx) => rx.test(to.path))) {
        window.location.href = config('app.api-url') + to.path;
        return false;
    }
    return to;
}
