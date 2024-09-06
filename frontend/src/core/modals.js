import _ from 'lodash';
import { ref, warn, markRaw } from 'vue';
import { arrRemoveId } from '@/core/utils.js';

export const globalModalsArr = ref([]);

export function destroyModal(tempId) {
    globalModalsArr.value = arrRemoveId(globalModalsArr.value, tempId, 'tempId');
}

export function createModal(modalObj) {
    const essentialKeys = [
        'component',
        'props',
        'val',
    ]; // attributes and listeners are non-essential

    // Helping developers get essentials right
    const missingEssentialKeys = essentialKeys.filter((key) => {
        return !_.has(modalObj, key);
    });

    if (missingEssentialKeys.length) {
        warn(`Missing essential keys in modal object:
            ${missingEssentialKeys.join(', ')}`);
        return;
    }

    // Temp id for reference
    const tempId = Date.now();

    // Listeners
    let listeners = modalObj.listeners || {};

    // Internal close modal function
    const internalCloseModalFn = () => {
        destroyModal(tempId);
    };

    // User-defined close modal callback
    const userCloseModalFn = modalObj.listeners?.closeModal;

    // Combine the internal and user-defined closeModal logic
    const combinedCloseModalFn = () => {
        if (userCloseModalFn) {
            userCloseModalFn();
        }
        internalCloseModalFn();
    };

    listeners = {
        ...listeners,
        closeModal: combinedCloseModalFn,
    };

    // To only have slots present when they are needed, otherwise null
    let slots = null;
    if (modalObj.slots) {
        slots = {};

        if (modalObj.slots.header) {
            slots.header = modalObj.slots.header;
        }
        if (modalObj.slots.description) {
            slots.description = modalObj.slots.description;
        }
    }

    const fullObj = {
        ...modalObj,
        listeners,
        slots,
        closeModalFn: internalCloseModalFn,
        tempId,
    };
    globalModalsArr.value.push(fullObj);
}

export function createModalFromComponent(componentImport, modalObj) {
    componentImport
        .then((module) => {
            createModal({
                ...modalObj,
                component: markRaw(module.default),
            });
        });
}
