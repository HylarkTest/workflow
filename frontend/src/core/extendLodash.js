import _ from 'lodash';
import {
    firstKey, getFirstKey, pascalCase, upperSnake, awaitCall, sleep, retry,
} from '@/core/utils.js';
import app from '@/app.js';

_.mixin({

    // Same as lodash _.set but using the vue observer set method that keeps
    // watchers up to date.
    vueSet(object, path, value) {
        if (!_.isObject(object)) {
            return object;
        }
        const pathArray = _.toPath(path);

        let index = -1;
        const length = pathArray.length;
        const lastIndex = length - 1;
        let nested = object;

        // eslint-disable-next-line no-plusplus
        while (nested != null && ++index < length) {
            const key = pathArray[index];
            let newValue;

            if (index !== lastIndex) {
                const objValue = nested[key];
                if (_.isObject(objValue)) {
                    newValue = objValue;
                } else {
                    newValue = _.isInteger(pathArray[index + 1]) ? [] : {};
                }
            } else {
                newValue = value;
            }
            app.set(nested, key, newValue);
            nested = nested[key];
        }
        return object;
    },
    pascalCase,
    upperSnake,
    firstKey,
    getFirstKey,
    awaitCall,
    sleep,
    retry,
});
