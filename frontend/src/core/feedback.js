import _ from 'lodash';
import { ref } from 'vue';
import { arrRemoveId } from '@/core/utils.js';

export const feedbackInfo = ref([]);

function pushFeedback(feedback) {
    feedbackInfo.value.push(feedback);
}

const debouncePushFeedback = _.debounce(pushFeedback, 2000, { leading: true, trailing: false });

export function addFeedback(feedback, isDebounced) {
    if (isDebounced) {
        debouncePushFeedback(feedback);
    } else {
        pushFeedback(feedback);
    }
}

export function closeFeedback(id) {
    feedbackInfo.value = arrRemoveId(feedbackInfo.value, id);
}
