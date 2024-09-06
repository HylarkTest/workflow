import { readonly, ref } from 'vue';
import axios from 'axios';

const loginHistory = ref();
let loginMeta;

let loadingRequest;

export default function getLoginHistory() {
    if (!loginHistory.value) {
        if (!loadingRequest) {
            loadingRequest = axios.get('/login-history').then((response) => {
                loginHistory.value = response.data.data;
                loginMeta = response.data.meta;
                return readonly(loginHistory);
            }).finally(() => {
                loadingRequest = null;
            });
        }

        return loadingRequest;
    }

    return Promise.resolve(readonly(loginHistory));
}

let nextPageRequest;

export function hasMoreLoginHistory() {
    return loginMeta && loginMeta.to !== loginMeta.total;
}

export function loadNextPageOfLoginHistory() {
    if (hasMoreLoginHistory()) {
        if (!nextPageRequest) {
            nextPageRequest = axios.get(`/login-history?page=${loginMeta.current_page + 1}`).then((response) => {
                loginHistory.value = loginHistory.value.concat(response.data.data);
                loginMeta = response.data.meta;
            }).finally(() => {
                nextPageRequest = null;
            });
        }
        return nextPageRequest;
    }
    return Promise.resolve();
}
