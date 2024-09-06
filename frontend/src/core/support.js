import { ref, unref } from 'vue';

export const supportInfo = ref([]);

export function addSupport(supportObj) {
    supportInfo.value.unshift(supportObj);
}

export function removeSupport(val) {
    supportInfo.value = supportInfo.value.filter((info) => unref(info).val !== val);
}

function buildSupportInfo(supportProps) {
    addSupport(supportProps);
}

export function callSupportInfo(supportProps) {
    buildSupportInfo(supportProps);
}

export default function install(baseApp) {
    // eslint-disable-next-line no-param-reassign
    baseApp.config.globalProperties.$callSupportInfo = callSupportInfo;
    // eslint-disable-next-line no-param-reassign
    baseApp.config.globalProperties.$removeSupport = removeSupport;
}
