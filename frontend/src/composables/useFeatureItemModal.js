import { toRefs, computed, h } from 'vue';
import { $t } from '@/i18n.js';
import { createModalFromComponent } from '@/core/modals.js';
import { featureIcons } from '@/core/display/featureIcons.js';

const sizing = {
    EVENTS: {
        size: 'w-800p',
    },
};

const propNaming = {
    LINKS: {
        item: 'link',
        list: 'linkList',
        formComponent: import('@/components/links/LinkForm.vue'),
    },
    TODOS: {
        item: 'todo',
        list: 'todoList',
        formComponent: import('@/components/todos/TodoForm.vue'),
    },
    EVENTS: {
        item: 'event',
        list: 'calendar',
        formComponent: import('@/components/events/EventForm.vue'),
        uneditableComponent: import('@/components/events/EventUneditable.vue'),
    },
    PINBOARD: {
        item: 'pin',
        list: 'pinboard',
        formComponent: import('@/components/pinboard/PinForm.vue'),
    },
    DOCUMENTS: {
        item: 'document',
        list: 'drive',
        formComponent: import('@/components/documents/DocumentForm.vue'),
    },
    NOTES: {
        item: 'note',
        list: 'notebook',
        formComponent: import('@/components/notes/NoteForm.vue'),
    },
};

export default (props) => {
    const {
        featureType,
        list = { value: null },
        item = { value: null },
        spaceId = { value: null },
        page = { value: null },
        defaultAssociations = { value: null },
    } = toRefs(props);

    const featureTypeFormatted = computed(() => _.camelCase(featureType.value));
    const featureInfo = computed(() => propNaming[featureType.value]);
    const formComponent = computed(() => featureInfo.value.formComponent);

    const featureItemFormKey = computed(() => featureInfo.value.item);
    const featureListFormKey = computed(() => featureInfo.value.list);

    function createFeatureFormModal(featureFormProps) {
        // These properties (formProps, isNew, isReadOnly, icon, header etc)
        // exist within this function rather than as separate computed/ref variables
        // so that components like FeatureContent.vue can declare featureFormProps from data.
        // This is not required if the component uses cAPI. FeatureContent.vue still uses oAPI
        // and is too big to refactor at the moment.

        const formProps = {
            class: 'px-4 pb-4',
            page: page.value,
            spaceId: spaceId.value,
            defaultAssociations: defaultAssociations.value,
            [featureItemFormKey.value]: item.value,
            [featureListFormKey.value]: list.value,
            ...featureFormProps,
        };

        const formItem = formProps[featureItemFormKey.value];
        const formList = formProps[featureListFormKey.value];

        const isNew = !formItem || _.isEmpty(formItem);
        const isReadOnly = formList?.isReadOnly;

        const icon = featureIcons[featureType.value].icon;

        function getHeaderTextPath() {
            let situation = 'edit';
            if (isNew) {
                situation = 'new';
            }
            if (isReadOnly) {
                situation = 'view';
            }
            return `features.${featureTypeFormatted.value}.headers.${situation}`;
        }

        createModalFromComponent(formComponent.value, {
            attributes: {
                containerClass: sizing[featureType.value]?.size || 'w-600p',
                header: true,
            },
            props: {
                ...formProps,
                isNew,
                isReadOnly,
            },
            val: 'itemFormModal',
            listeners: {
            },
            slots: {
                header: () => {
                    return h('div', {}, [
                        h('i', {
                            class: `fa-regular mr-2 ${icon}`,
                        }),
                        h('span', {}, $t(getHeaderTextPath())),
                    ]);
                },
            },
        });
    }
    return {
        featureItemFormKey,
        featureListFormKey,
        formComponent,

        createFeatureFormModal,
    };
};
