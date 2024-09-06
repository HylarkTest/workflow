import { toRefs, computed } from 'vue';

import {
    timezone,
} from '@/core/repositories/preferencesRepository.js';

export default (props) => {
    const {
        noTimezone = { value: false },
    } = toRefs(props);

    const timezonePreference = computed(() => (noTimezone.value ? 'UTC' : timezone.value));

    return {
        // timezone is not reactive? fine for now but may need to change
        timezone: timezonePreference.value,
    };
};
