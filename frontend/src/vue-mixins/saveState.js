import {
    forEach, isFunction, isObject, pickBy, some, throttle,
} from 'lodash';
import * as local from '@/core/localStorage.js';
import * as server from '@/core/serverStorage.js';

export default {
    data() {
        return {
            loadingState: true,
        };
    },
    methods: {
        getStore() {
            return this.$options.saveState?.store === 'server' ? server : local;
        },
        async loadState() {
            this.loadingState = true;
            const savedState = await this.getStore().get(
                this.getSaveStateCacheKey(),
                this.$options.saveState?.isEssential ? 'essential' : 'functional'
            );

            if (!savedState) {
                return;
            }

            forEach(savedState, (value, key) => {
                if (this.attributeIsManagedBySaveState(key)) {
                    if (isFunction(this.$options.saveState?.onSaveStateLoad)) {
                        // eslint-disable-next-line no-param-reassign
                        value = this.$options.saveState.onSaveStateLoad.call(this, key, value, this.$data[key]);
                    }

                    this.$data[key] = value;
                }
            });
            this.loadingState = false;
        },

        saveStateMethod() {
            const data = pickBy(this.$data, (value, attribute) => {
                return this.attributeIsManagedBySaveState(attribute);
            });

            this.getStore().store(
                this.getSaveStateCacheKey(),
                data,
                this.$options.saveState?.isEssential ? 'essential' : 'functional'
            );
        },

        attributeIsManagedBySaveState(attribute) {
            if (!this.$options.saveState?.propertiesForSave) {
                return false;
            }

            return some(
                this.$options.saveState?.propertiesForSave,
                (property) => (isObject(property) ? property.key === attribute : property === attribute)
            );
        },

        clearSavedState() {
            this.getStore().clear(this.getSaveStateCacheKey());
        },

        getSaveStateCacheKey() {
            return this.$options.saveState?.cacheKey || this.$options.name;
        },
    },

    async created() {
        await this.loadState();
        if (typeof this.$options.saveState?.afterSavedStateLoaded === 'function') {
            this.$options.saveState.afterSavedStateLoaded.call(this);
        }

        const throttleTime = this.$options.saveState?.throttle || 2000;
        const saveState = throttle(this.saveStateMethod, throttleTime, { leading: false, trailing: true });

        forEach(this.$options.saveState?.propertiesForSave, (attribute) => {
            let key;
            let options;
            if (isObject(attribute)) {
                key = attribute.key;
                options = attribute;
            } else {
                key = attribute;
                options = {};
            }
            this.$watch(key, saveState, options);
        });
    },

};
