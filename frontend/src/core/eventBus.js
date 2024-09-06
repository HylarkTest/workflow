import { get } from 'lodash';
import { arrRemove } from '@/core/utils.js';

export class EventBus {
    constructor() {
        this.listeners = {};
    }

    listen(event, handler) {
        const existingListeners = this.listeners[event] || [];
        this.listeners[event] = [...existingListeners, handler];
    }

    drop(event, handler) {
        if (this.listeners[event]) {
            this.listeners[event] = arrRemove(this.listeners[event], handler);
        }
    }

    dispatch(event, payload) {
        if (this.listeners[event]) {
            this.listeners[event].forEach((handler) => {
                handler(payload);
            });
        }
    }
}

const eventBus = new EventBus();

export default eventBus;

export function dispatchPromise(promise, event, path = '') {
    return promise.then((result) => {
        eventBus.dispatch(event, get(result, path));
        return result;
    });
}
