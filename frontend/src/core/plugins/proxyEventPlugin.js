import _ from 'lodash';

const plugin = {
    install(app) {
        // eslint-disable-next-line no-param-reassign
        app.config.globalProperties.$proxyEvent = function proxyEvent(
            $event,
            value,
            key,
            eventName = 'update:modelValue'
        ) {
            let emittedValue;

            if (key) {
                const clone = _.cloneDeep(value || {});
                _.set(clone, key, $event);
                emittedValue = clone;
            } else {
                emittedValue = $event;
            }
            return this.$emit(eventName, emittedValue);
        };
    },
};

export default plugin;
