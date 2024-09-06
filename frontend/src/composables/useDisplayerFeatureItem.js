import {
    toRefs,
    computed,
    reactive,
    watch,
} from 'vue';

import {
    getCombo,
} from '@/core/display/displayerInstructions.js';

import useFeatureItemModal from '@/composables/useFeatureItemModal.js';

export default (props) => {
    const {
        dataInfo,
        dataValue = { value: null },
        list = { value: null },
        page = { value: null },
        item = { value: null },
        isModifiable = { value: false },
    } = toRefs(props);

    const featureKey = computed(() => dataInfo.value.id);
    const featureFormatted = computed(() => _.camelCase(featureKey.value));
    const featureAddTextPath = computed(() => `features.${featureFormatted.value}.add`);

    const cantModifyClass = computed(() => { return !isModifiable.value ? 'pointer-events-none' : 'cursor-pointer'; });

    const combo = computed(() => dataInfo.value?.combo || 1);
    const selectedCombo = computed(() => getCombo(featureKey.value, combo.value));
    const displayClasses = computed(() => {
        if (!selectedCombo.value) {
            return '';
        }
        if (_.isObject(selectedCombo.value)) {
            return selectedCombo.value.classes;
        }
        return selectedCombo.value;
    });

    const defaultAssociations = computed(() => {
        return [{
            ...item.value,
            doNotOpen: true,
            doNotRemove: true,
        }];
    });

    // reactive/watch are used instead of computed so that they can be treated as refs for useFeatureItemModal props
    const createModalProps = reactive({
        featureType: featureKey.value,
        list: list.value,
        item: dataValue.value,
        page: page.value,
        defaultAssociations: defaultAssociations.value,
    });
    watch(() => featureKey.value, (value) => { createModalProps.featureType = value; });
    watch(() => list.value, (value) => { createModalProps.list = value; });
    watch(() => dataValue.value, (value) => { createModalProps.item = value; });
    watch(() => page.value, (value) => { createModalProps.page = value; });
    watch(() => defaultAssociations.value, (value) => { createModalProps.defaultAssociations = value; });

    const { createFeatureFormModal } = useFeatureItemModal(createModalProps);

    return {
        featureKey,
        featureFormatted,
        featureAddTextPath,

        cantModifyClass,
        displayClasses,

        defaultAssociations,

        createFeatureFormModal,
    };
};
